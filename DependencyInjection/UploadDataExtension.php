<?php

namespace Manuel\Bundle\UploadDataBundle\DependencyInjection;

use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class UploadDataExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('upload_data.files_dir', $config['files_dir']);
        $container->setParameter('upload_data.templates', $config['templates']);
        $container->setParameter('upload_data.secuity.debugging_role', $config['debugging_role']);

        $container->setAlias('upload_data.file_helper.default', $config['uploaded_file_helper']);

        $container->registerForAutoconfiguration(UploadConfig::class)->addTag('upload_data.config');
    }
}