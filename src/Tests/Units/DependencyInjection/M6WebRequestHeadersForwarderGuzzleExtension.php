<?php

namespace M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\Tests\Units\DependencyInjection;

use M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\EventListener\RequestHeadersForwarderListener;
use M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\Tests\ContainerTrait;
use mageekguy\atoum;

class M6WebRequestHeadersForwarderGuzzleExtension extends atoum\test
{
    use ContainerTrait;

    /**
     * test minimal configuration for bundle
     */
    public function testMinimalConfiguration()
    {
        $this
            ->given($this->initContainer('minimal', true))
            ->then
                ->boolean($this->container->has('test.guzzle1'))
                    ->isTrue()
                ->object($this->container->get('m6web.request_headers_forwarder_guzzle.listener.request_headers_forwarder'))
                    ->isInstanceOf(RequestHeadersForwarderListener::class)
        ;
    }
}
