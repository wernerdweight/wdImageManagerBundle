<?php

namespace WernerDweight\ImageManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {

	public function getConfigTreeBuilder() : TreeBuilder {
		$treeBuilder = new TreeBuilder();

		$rootNode = $treeBuilder->root('wd_image_manager');

		$this->addVersionsConfiguration($rootNode);

		return $treeBuilder;
	}

	private function addVersionsConfiguration(ArrayNodeDefinition $node) : void {
		$supportedTypes = ['jpg', 'png', 'gif'];

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
									->ifNotInArray($supportedTypes)
									->thenInvalid('The type %s is not supported. Please choose one of '.json_encode($supportedTypes).' or leave this option unset to keep original file type.')
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
