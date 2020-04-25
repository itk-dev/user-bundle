<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\Util;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\UserBundle\Exception\UserNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class UserManager //extends BaseUserManager
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Environment */
    private $twig;

    /** @var RouterInterface */
    private $router;

    /** @var RoleHierarchyInterface */
    private $roleHierarchy;

    /** @var array */
    private $configuration;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        Environment $twig,
        RouterInterface $router,
        RoleHierarchyInterface $roleHierarchy,
        array $configuration
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->router = $router;
        $this->roleHierarchy = $roleHierarchy;
        $this->configuration = $configuration;
    }

    public function find(string $username): ?UserInterface
    {
        return $this->entityManager->getRepository($this->getUserClass())->findOneBy(['username' => $username]);
    }

    public function createUser(): UserInterface
    {
        $user = new $this->configuration['class']();

        return $user;
    }

    public function getRoles(): array
    {
        return $this->roleHierarchy->getReachableRoleNames([$this->getSuperAdminRole()]);
    }

    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->entityManager->persist($user);
        if ($andFlush) {
            $this->entityManager->flush();
        }
//
//        if ($isNew && $this->getNotifyUserOnCreate()) {
//            // @TODO: Add flash bag message here?
//            $this->notifyUserCreated($user);
//        }
    }

    public function getNotifyUserOnCreate()
    {
        return $this->configuration['notify_user_on_create'];
    }

    public function notifyUserCreated(User $user, $andFlush = true, array $options = [])
    {
        if (null === $user->getConfirmationToken()) {
            // @var $tokenGenerator TokenGeneratorInterface
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        $user->setPasswordRequestedAt(new \DateTime());
        // We use parent here to prevent notifying user.
        parent::updateUser($user, $andFlush);
        $message = $this->createUserCreatedMessage($user, $options);

        $this->mailer->send($message);
    }

    private function createUserCreatedMessage(UserInterface $user, array $options = [])
    {
        $url = $this->router->generate('fos_user_resetting_reset', [
            'token' => $user->getConfirmationToken(),
            'create' => true,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        $sender = $this->configuration['sender'];
        $config = $this->configuration['user_created'];
        $context = [
            'reset_password_url' => $url,
            'user' => $user,
            'sender' => $sender,
        ]
            + $this->configuration['user_created']
            + $this->configuration;

        $subject = $this->twig->createTemplate($config['subject'])->render($context);
        $header = $this->twig->createTemplate($config['header'])->render($context);
        $message = $options['message'] ?? $config['message'] ?? null;
        if (null !== $message) {
            $message = $this->twig->createTemplate($message)->render($context);
        }
        $body = $this->twig->createTemplate($config['body'])->render($context);
        $buttonText = $this->twig->createTemplate($config['button']['text'])->render($context);
        $footer = $this->twig->createTemplate($config['footer'])->render($context);

        $body = $this->twig->render('@ItkDevUser/email/user/user_created_user.html.twig', [
            'reset_password_url' => $url,
            'header' => $header,
            'message' => $message,
            'body' => $body,
            'button' => [
                'url' => $url,
                'text' => $buttonText,
            ],
            'footer' => $footer,
        ]);

        return (new \Swift_Message($subject))
            ->setFrom($sender['email'], $sender['name'])
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');
    }

    public function resetPassword(UserInterface $user, $andFlush = true)
    {
        if (null === $user->getConfirmationToken()) {
            // @var $tokenGenerator TokenGeneratorInterface
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        $user->setPasswordRequestedAt(new \DateTime());
        $this->updateUser($user, $andFlush);

        $this->userMailer->sendResettingEmailMessage($user);
    }

    public function setRoles(UserInterface $user, array $roles): UserInterface
    {
        if (!method_exists($user, 'setRoles')) {
            throw new \RuntimeException('Cannot set roles on user');
        }
        $user->setRoles($roles);

        return $user;
    }

    public function getSuperAdminRole(): string
    {
        return 'ROLE_SUPER_ADMIN';
    }

    public function addRoles(UserInterface $user, array $roles): UserInterface
    {
        $roles = array_unique(array_merge($user->getRoles(), $roles));

        return $this->setRoles($user, $roles);
    }

    public function removeRoles(UserInterface $user, array $roles): UserInterface
    {
        $roles = array_unique(array_diff($user->getRoles(), $roles));

        return $this->setRoles($user, $roles);
    }

    public function findUserByUsername(string $username, bool $mustExist): ?UserInterface
    {
        $user = $this->getRepository()->findOneBy([$this->getUsernameField() => $username]);
        if ($mustExist && null === $user) {
            throw new UserNotFoundException(sprintf('User with username %s does not exist', $username));
        }

        return $user;
    }

    public function findUserBy(array $criteria): ?UserInterface
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @param null $limit
     * @param null $offset
     *
     * @return array|UserInterface[]
     */
    public function findUsersBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    private function getRepository()
    {
        return $this->entityManager->getRepository($this->getUserClass());
    }

    private function getUserClass()
    {
        return $this->configuration['user_class'];
    }

    private function getUsernameField()
    {
        return $this->configuration['username_field'];
    }
}
