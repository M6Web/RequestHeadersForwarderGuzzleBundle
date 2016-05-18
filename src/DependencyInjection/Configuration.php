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
            ->children()

                ->arrayNode('clients')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('client')
                    ->prototype('array')
                        ->children()

                            ->arrayNode('headers')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('header')
                                ->prototype('scalar')->end()
                            ->end()

                        ->end()
                    ->end()
                ->end()

            ->end();

        return $treeBuilder;
    }
}
