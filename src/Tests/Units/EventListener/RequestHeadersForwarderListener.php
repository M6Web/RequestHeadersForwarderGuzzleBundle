<?php

namespace M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\Tests\Units\EventListener;

use GuzzleHttp;
use mageekguy\atoum;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * RequestHeadersForwarderListener test class.
 */
class RequestHeadersForwarderListener extends atoum\test
{
    /**
     * Test onKernelRequest method.
     */
    public function testOnKernelRequest()
    {
        $this
            ->given(
                // We have a mocked guzzle client which is configured to return a 200
                $guzzleClient = new GuzzleHttp\Client(['handler' => GuzzleHttp\HandlerStack::create(
                    new GuzzleHttp\Handler\MockHandler([new GuzzleHttp\Psr7\Response(200)])
                )]),

                // We set a kind of "spy" on the guzzle client to be able to retrieve sent requests headers later.
                $guzzleClientRequests = [],
                $guzzleClient->getConfig('handler')->push(GuzzleHttp\Middleware::history($guzzleClientRequests)),

                // We create the request listener configured to forward X-OAUTH-SAMPLE header to the guzzle client
                $this->newTestedInstance([
                    'test.guzzle.client' => [
                        'instance' => $guzzleClient,
                        'headers' => ['X-OAUTH-SAMPLE'],
                    ],
                ]),

                // We create a mocked event containing returning a mocked request containing "X-OAUTH-SAMPLE" and "Random-Header" header
                $headerBag = new HeaderBag(),
                $headerBag->add([
                    'X-OAUTH-SAMPLE' => 'some value',
                    'Random-Header' => 'RandomFTW',
                ]),
                $mockedEvent = $this->getEventMock([
                    'getRequest' => $this->getRequestMock([
                        'headers' => $headerBag,
                    ])
                ])
            )
            ->if(
                // We call the onKernelRequest tested method with the mocked event
                $this->testedInstance->onKernelRequest($mockedEvent)
            )
            ->and(
                // The guzzle client sends a request after the onKernelRequest method was called
                $guzzleClient->get('something')
            )
            ->then
                // The guzzle client request should have been sent with the forwarded headers
                ->array($guzzleClientSentHeaders = $guzzleClientRequests[0]['request']->getHeaders())
                    ->notHasKey('Random-Header')
                    ->hasKey('X-OAUTH-SAMPLE')
                ->array($guzzleClientSentHeaders['X-OAUTH-SAMPLE'])
                    ->contains('some value')
        ;
    }

    /**
     * @param array $mockedMethodsWithResult as [ 'mockedMethod' => MOCKED_RESULT ]
     *
     * @return \Symfony\Component\HttpKernel\Event\GetResponseEvent
     */
    protected function getEventMock(array $mockedMethodsWithResult = [])
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        $mock = new \mock\Symfony\Component\HttpKernel\Event\GetResponseEvent();
        foreach ($mockedMethodsWithResult as $mockedMethod => $mockedResult) {
            $mock->getMockController()->$mockedMethod = $mockedResult;
        }

        return $mock;
    }

    /**
     * @param array $mockedPropertiesWithResult as [ 'mockedProperty' => MOCKED_RESULT ]
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequestMock(array $mockedPropertiesWithResult = [])
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        $mock = new \mock\Symfony\Component\HttpFoundation\Request();
        foreach ($mockedPropertiesWithResult as $mockedProperty => $mockedResult) {
            $mock->$mockedProperty = $mockedResult;
        }

        return $mock;
    }
}
