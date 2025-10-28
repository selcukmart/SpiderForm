<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Form Generator Bundle Configuration
 *
 * @author selcukmart
 * @since 2.0.0
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('form_generator');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('default_theme')
                    ->defaultValue('bootstrap5')
                    ->info('Default theme to use for forms')
                ->end()
                ->scalarNode('default_renderer')
                    ->defaultValue('twig')
                    ->info('Default renderer engine (twig or smarty)')
                ->end()
                ->arrayNode('template_paths')
                    ->prototype('scalar')->end()
                    ->defaultValue([])
                    ->info('Additional template paths')
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Enable template caching')
                        ->end()
                        ->scalarNode('dir')
                            ->defaultValue('%kernel.cache_dir%/form_generator')
                            ->info('Cache directory')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('csrf_enabled')
                            ->defaultTrue()
                            ->info('Enable CSRF protection')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
