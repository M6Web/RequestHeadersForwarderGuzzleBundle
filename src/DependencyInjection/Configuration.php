<?php

namespace M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('m6_web_request_headers_forwarder_guzzle');

        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->arrayNode('clients')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->fixXmlConfig('header')
                        ->children()
                            ->arrayNode('headers')
                                ->defaultValue([])
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->children()
                            ->arrayNode('queries_to_headers')
                                ->defaultValue([])
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->requiresAtLeastOneElement()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
