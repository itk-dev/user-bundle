<?php
/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */
namespace ItkDev\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class ItkDevUserBundleExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(\dirname(__DIR__));

        $container->loadFromExtension('twig', [
            'paths' => [
                $fileLocator->locate('Resources/views/bundles/FOSUser') => 'FOSUser',
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $builder)
    {
        // Nothing here
    }
}
