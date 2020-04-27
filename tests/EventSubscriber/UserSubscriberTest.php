<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\EventSubscriber;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use ItkDev\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSubscriberTest extends TestCase
{
    public function testPostPersist()
    {
        $user = (new User())
            ->setEmail('test@example.com');
        $args = $this->createMock(LifecycleEventArgs::class);
        $args->expects($this->once())->method('getObject')->willReturn($user);

        $subscriber = new UserSubscriber($userManager);
        $subscriber->postPersist($args);
        $object = $args->getObject();
        if ($object instanceof UserInterface) {
            $this->userManager->userCreated($object);
        }
    }
}
