<?php

namespace WernerDweight\ImageManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WDImageManagerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('wd_image_manager.versions', $config['versions']);
        $container->setParameter('wd_image_manager.upload_root', $config['upload_root']);
        $container->setParameter('wd_image_manager.upload_path', $config['upload_path']);
        $container->setParameter('wd_image_manager.secret', $config['secret']);
        $container->setParameter('wd_image_manager.autorotate', $config['autorotate']);
    }
}
