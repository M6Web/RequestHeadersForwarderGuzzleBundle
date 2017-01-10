<?php

namespace M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\EventListener;

use GuzzleHttp;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class RequestHeadersForwarderListener listening for kernel.request event.
 *
 * @package M6\Bundle\M6Video\PlayerBundle\EventListener
 */
class RequestHeadersForwarderListener
{
    /**
     * @var array As [ ['instance' => GuzzleHttp\ClientInterface, 'headers' => ['x-header-1'] ] ]
     */
    private $clients;

    /**
     * Constructor.
     *
     * @param array $clients
     */
    public function __construct(array $clients)
    {
        foreach ($clients as $client) {
            if (!isset($client['instance']) || !$client['instance'] instanceof GuzzleHttp\ClientInterface) {
                throw new \InvalidArgumentException('Only Guzzle clients are supported.');
            }
        }

        $this->clients = $clients;
    }

    /**
     * Will forward request headers respectively for each clients.
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $requestHeaders = $event->getRequest()->headers;

        // Forward headers to each client
        foreach ($this->clients as $client) {

            // Define forwarded headers names and values
            $forwardedHeaders = [];
            foreach ($client['headers'] as $header) {
                if ($requestHeaders->has($header)) {
                    $forwardedHeaders[] = [
                        'name' => $header,
                        'value' => $requestHeaders->get($header),
                    ];
                }
            }

            // If there is nothing to forward, return.
            if (empty($forwardedHeaders)) {
                continue;
            }

            // Forward headers to each client
            $client['instance']->getConfig('handler')->unshift(GuzzleHttp\Middleware::mapRequest(
                function (RequestInterface $request) use ($forwardedHeaders) {

                    foreach ($forwardedHeaders as $header) {
                        $request = $request->withHeader($header['name'], $header['value']);
                    }

                    return $request;
                })
            );
        }
    }
}
