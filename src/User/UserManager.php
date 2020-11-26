<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\User;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use InvalidArgumentException;
use ItkDev\UserBundle\Exception\UserNotFoundException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface as CoreUserInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Twig\Environment;

class UserManager
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var ResetPasswordHelperInterface */
    private $resetPasswordHelper;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Environment */
    private $twig;

    /** @var MailerInterface */
    private $mailer;

    /** @var RouterInterface */
    private $router;

    /** @var RoleHierarchyInterface */
    private $roleHierarchy;

    /** @var array */
    private $configuration;

    public function setResetPasswordHelper(ResetPasswordHelperInterface $resetPasswordHelper): void
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        Environment $twig,
        MailerInterface $mailer,
        RouterInterface $router,
        RoleHierarchyInterface $roleHierarchy,
        array $configuration
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->roleHierarchy = $roleHierarchy;
        $this->configuration = $configuration;
    }

    public function createUser(string $username = null): CoreUserInterface
    {
        $class = $this->getUserClass();
        /** @var CoreUserInterface $user */
        $user = new $class();
        $this->setPassword($user, uniqid('', true));

        if (null !== $username) {
            if (null !== $this->findUserByUsername($username, false)) {
                throw new InvalidArgumentException(sprintf('User with username %s already exists', $username));
            }
            $user->setField($user, $this->getUsernameField(), $username);
        }

        return $user;
    }

    public function userCreated(CoreUserInterface $user)
    {
        if ($this->getNotifyUserOnCreate()) {
            $this->notifyUserCreated($user);
        }
    }

    public function userUpdated(CoreUserInterface $user)
    {
    }

    public function userLoggedIn(CoreUserInterface $user)
    {
        if ($user instanceof \ItkDev\UserBundle\User\UserInterface) {
            $user->setLastLoggedInAt(new DateTimeImmutable());
            $this->updateUser($user);
        }
    }

    public function getRoles(): array
    {
        return $this->roleHierarchy->getReachableRoleNames([$this->getSuperAdminRole()]);
    }

    public function updateUser(CoreUserInterface $user, $andFlush = true)
    {
        $this->entityManager->persist($user);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    public function getNotifyUserOnCreate()
    {
        return $this->configuration['notify_user_on_create'];
    }

    public function notifyUserCreated(CoreUserInterface $user, $andFlush = true, array $options = [])
    {
        if (null !== $this->resetPasswordHelper) {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
            $url = $this->router->generate('app_reset_password', [
                'token' => $resetToken->getToken(),
                'create' => true,
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $sender = $this->configuration['sender'];
            $config = $this->configuration['user_created'];

            $context = [
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

            $email = (new TemplatedEmail())
                ->from(new Address($sender['email'], $sender['name']))
                ->to($user->getEmail())
                ->subject($subject)
                ->htmlTemplate('email/user/user_created.html.twig')
                ->context([
                    'resetToken' => $resetToken,
                    'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
                    'reset_password_url' => $url,
                    'header' => $header,
                    'message' => $message,
                    'body' => $body,
                    'button' => [
                        'url' => $url,
                        'text' => $buttonText,
                    ],
                    'footer' => $footer,
                ])
            ;

            $this->mailer->send($email);
        }
    }

//    private function createUserCreatedMessage(CoreUserInterface $user, ResetPasswordToken $token, array $options = [])
//    {
//        $url = $this->router->generate('fos_user_resetting_reset', [
//            'token' => $user->getConfirmationToken(),
//            'create' => true,
//        ], UrlGeneratorInterface::ABSOLUTE_URL);
//        $sender = $this->configuration['sender'];
//        $config = $this->configuration['user_created'];
//        $context = [
//            'reset_password_url' => $url,
//            'user' => $user,
//            'sender' => $sender,
//        ]
//            + $this->configuration['user_created']
//            + $this->configuration;
//
//        $subject = $this->twig->createTemplate($config['subject'])->render($context);
//        $header = $this->twig->createTemplate($config['header'])->render($context);
//        $message = $options['message'] ?? $config['message'] ?? null;
//        if (null !== $message) {
//            $message = $this->twig->createTemplate($message)->render($context);
//        }
//        $body = $this->twig->createTemplate($config['body'])->render($context);
//        $buttonText = $this->twig->createTemplate($config['button']['text'])->render($context);
//        $footer = $this->twig->createTemplate($config['footer'])->render($context);
//
//        $body = $this->twig->render('@ItkDevUser/email/user/user_created_user.html.twig', [
//            'reset_password_url' => $url,
//            'header' => $header,
//            'message' => $message,
//            'body' => $body,
//            'button' => [
//                'url' => $url,
//                'text' => $buttonText,
//            ],
//            'footer' => $footer,
//        ]);
//
//        return (new \Swift_Message($subject))
//            ->setFrom($sender['email'], $sender['name'])
//            ->setTo($user->getEmail())
//            ->setBody($body, 'text/html');
//    }

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

    public function setPassword(UserInterface $user, string $password): UserInterface
    {
        return $this->setField($user, 'password', $this->passwordEncoder->encodePassword($user, $password));
    }

    public function setRoles(CoreUserInterface $user, array $roles): CoreUserInterface
    {
        return $this->setField($user, 'roles', $roles);
    }

    private function setField(CoreUserInterface $user, string $field, $value): CoreUserInterface
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        if (!$propertyAccessor->isWritable($user, $field)) {
            throw new \RuntimeException(sprintf('Cannot set %s on user', $field));
        }
        $propertyAccessor->setValue($user, $field, $value);

        return $user;
    }

    public function getSuperAdminRole(): string
    {
        return 'ROLE_SUPER_ADMIN';
    }

    public function addRoles(CoreUserInterface $user, array $roles): CoreUserInterface
    {
        $roles = array_unique(array_merge($user->getRoles(), $roles));

        return $this->setRoles($user, $roles);
    }

    public function removeRoles(CoreUserInterface $user, array $roles): CoreUserInterface
    {
        $roles = array_unique(array_diff($user->getRoles(), $roles));

        return $this->setRoles($user, $roles);
    }

    public function findUserByUsername(string $username, bool $mustExist = true): ?CoreUserInterface
    {
        $user = $this->getRepository()->findOneBy([$this->getUsernameField() => $username]);
        if ($mustExist && null === $user) {
            throw new UserNotFoundException(sprintf('User with username %s does not exist', $username));
        }

        return $user;
    }

    public function findUserBy(array $criteria): ?CoreUserInterface
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @param null $limit
     * @param null $offset
     *
     * @return array|CoreUserInterface[]
     */
    public function findUsersBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository($this->getUserClass());
    }

    private function getUserClass(): string
    {
        return $this->configuration['user_class'];
    }

    private function getUsernameField(): string
    {
        return $this->configuration['username_field'];
    }

    private function getPasswordField(): string
    {
        return $this->configuration['password_field'];
    }
}
