<?php
declare(strict_types=1);

namespace WernerDweight\ImageManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WDImageManagerExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
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
