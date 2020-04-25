<?php

/*
 * This file is part of itk-dev/user-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserBundle\DependencyInjection;

use ItkDev\UserBundle\User\UserManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ItkDevUserExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(\dirname(__DIR__));

        $templatePaths = [
            '' => null,
            'bundles/FOSUser' => 'FOSUser',
        ];

        $projectTemplatesPath = $container->getParameter('kernel.project_dir').'/templates';
        foreach ($templatePaths as $path => $name) {
            if (is_dir($projectTemplatesPath.'/'.$path)) {
                // Allow project to overwrite bundle templates.
                $paths[$projectTemplatesPath.'/'.$path] = $name;
            }
            $paths[$fileLocator->locate('Resources/views/'.$path)] = $name;
        }

//        header('content-type: text/plain'); echo var_export($paths, true); die(__FILE__.':'.__LINE__.':'.__METHOD__);

        $container->loadFromExtension('twig', [
            'paths' => $paths,
        ]);
    }

    public function load(array $configs, ContainerBuilder $builder)
    {
        $loader = new XmlFileLoader($builder, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $definition = $builder->getDefinition(UserManager::class);
        $definition->replaceArgument('$configuration', $config);
    }
}
