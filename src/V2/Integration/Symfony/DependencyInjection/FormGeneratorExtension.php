<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Form Generator Extension for Symfony DI
 *
 * @author selcukmart
 * @since 2.0.0
 */
class FormGeneratorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set parameters
        $container->setParameter('form_generator.default_theme', $config['default_theme']);
        $container->setParameter('form_generator.default_renderer', $config['default_renderer']);
        $container->setParameter('form_generator.template_paths', $config['template_paths']);
        $container->setParameter('form_generator.cache_enabled', $config['cache']['enabled']);
        $container->setParameter('form_generator.cache_dir', $config['cache']['dir']);
        $container->setParameter('form_generator.csrf_enabled', $config['security']['csrf_enabled']);

        // Load services
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        // $loader->load('services.php'); // Would be created if needed
    }

    public function getAlias(): string
    {
        return 'form_generator';
    }
}
