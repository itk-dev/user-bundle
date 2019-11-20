<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\DependencyInjection;

use ItkDev\UserBundle\Doctrine\UserManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ItkDevUserExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(\dirname(__DIR__));

        $projectTemplatesPath = $container->getParameter('kernel.project_dir').'/templates/bundles';
        $paths = [
            $fileLocator->locate('Resources/views/bundles/FOSUser') => 'FOSUser',
        ];

        if (is_dir($projectTemplatesPath.'/FOSUserBundle')) {
            // Allow project to overwrite bundle templates.
            $paths = array_merge([$projectTemplatesPath.'/FOSUserBundle' => 'FOSUser'], $paths);
        }

        $container->loadFromExtension('twig', [
            'paths' => $paths,
        ]);
    }

    public function load(array $configs, ContainerBuilder $builder)
    {
        $loader = new YamlFileLoader($builder, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $definition = $builder->getDefinition(UserManager::class);
        $definition->replaceArgument('$configuration', $config);
    }
}
