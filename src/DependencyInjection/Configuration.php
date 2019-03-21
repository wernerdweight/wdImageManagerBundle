<?php
declare(strict_types=1);

namespace WernerDweight\ImageManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /** @var string[] */
    private const SUPPORTED_TYPES = ['jpg', 'png', 'gif'];

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('wd_image_manager');
        $this->addVersionsConfiguration($rootNode);
        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addVersionsConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('upload_root')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('upload_path')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('autorotate')->defaultFalse()->end()
                ->arrayNode('versions')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')
                                ->defaultNull()
                                ->validate()
                                    ->ifNotInArray(self::SUPPORTED_TYPES)
                                    ->thenInvalid(
                                        \Safe\sprintf(
                                            'The type %%s is not supported. Please choose one of %s or leave this option unset to keep original file type.',
                                            \Safe\json_encode(self::SUPPORTED_TYPES)
                                        )
                                    )
                                ->end()
                            ->end()
                            ->integerNode('width')
                                ->min(0)
                                ->defaultValue(0)
                            ->end()
                            ->integerNode('height')
                                ->min(0)
                                ->defaultValue(0)
                            ->end()
                            ->integerNode('quality')
                                ->min(0)
                                ->max(100)
                                ->defaultValue(75)
                            ->end()
                            ->booleanNode('crop')->defaultFalse()->end()
                            ->booleanNode('encrypted')->defaultFalse()->end()
                            ->arrayNode('watermark')
                                ->children()
                                    ->scalarNode('file')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('size')->end()
                                    ->arrayNode('position')
                                        ->children()
                                            ->integerNode('top')
                                                ->isRequired()
                                                ->min(0)
                                                ->max(100)
                                            ->end()
                                            ->integerNode('left')
                                                ->isRequired()
                                                ->min(0)
                                                ->max(100)
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
