<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\EventSubscriber;

use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSubscriber implements EventSubscriberInterface
{
    /** @var array */
    private $configuration;

    public function __construct(array $configuration)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->configuration = $configuration;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::postPersist => 'postPersist',
        ];
    }

    public function postPersist(array $event)
    {
    }
}
