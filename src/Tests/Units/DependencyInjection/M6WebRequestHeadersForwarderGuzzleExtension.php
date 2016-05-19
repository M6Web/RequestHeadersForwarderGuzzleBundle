<?php

namespace M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\Tests\Units\DependencyInjection;

use M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\EventListener\RequestHeadersForwarderListener;
use M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\Tests\ContainerTrait;
use mageekguy\atoum;

class M6WebRequestHeadersForwarderGuzzleExtension extends atoum\test
{
    use ContainerTrait;

    /**
     * Test minimal configuration for bundle
     *
     * @param string $configFormat
     *
     * @dataProvider minimalConfigurationProvider
     */
    public function testMinimalConfiguration($configFormat)
    {
        $this
            ->given(
                $this->initContainer('minimal', $configFormat),
                $requestEventListenerId = 'm6web.request_headers_forwarder_guzzle.listener.request_headers_forwarder'
            )
            ->if(
                $config = $this->config,
                $container = $this->container
            )
            ->then
                // Bundle configuration
                ->array($clients = $config['clients'])
                    ->array($guzzleClient1 = $clients['test.guzzle1'])
                        ->array($guzzleClient1['headers'])
                            ->containsValues(['x-auth-sample', 'something'])
                    ->array($guzzleClient2 = $clients['test.guzzle2'])
                        ->array($guzzleClient2['headers'])
                            ->containsValues(['something-else', 'oauth-token'])

                // Bundle generated services
                ->object($definition = $container->getDefinition($requestEventListenerId))
                    ->boolean($definition->hasTag('kernel.event_listener'))
                        ->isTrue()
                    ->array($definition->getTag('kernel.event_listener')[0])
                        ->string['event']
                            ->isEqualTo('kernel.request')
                ->object($container->get($requestEventListenerId))
                    ->isInstanceOf(RequestHeadersForwarderListener::class)
        ;
    }

    /**
     * Data provider for testMinimalConfiguration()
     *
     * @return array
     */
    protected function minimalConfigurationProvider()
    {
        return [
            // Yaml
            ['yaml'],

            // Xml
            ['xml'],
        ];
    }
}
