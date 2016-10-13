<?php

namespace M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\EventListener;

use GuzzleHttp;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class RequestHeadersForwarderListener listening for kernel.request event.
 *
 * @package M6\Bundle\M6Video\PlayerBundle\EventListener
 */
class RequestHeadersForwarderListener
{
    /**
     * @var array As [ ['instance' => GuzzleHttp\Client, 'headers' => ['x-header-1'], 'queries_to_headers' => ['X-Auth-user-param' => ['param1', 'param2'] ] ] ]
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
            if (!isset($client['instance']) || !$client['instance'] instanceof GuzzleHttp\Client) {
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
        $queries = $event->getRequest()->query->all();

        // Forward headers to each client
        foreach ($this->clients as $client) {

            // Define forwarded headers names and values
            $forwardedHeaders = [];
            $this->retrieveHeaders($requestHeaders, $client['headers'], $forwardedHeaders);
            $this->retrieveHeadersFromQueries($queries, $client['queries_to_headers'], $forwardedHeaders);

            // If there is nothing to forward, return.
            if (empty($forwardedHeaders)) {
                continue;
            }

            $this->forwardHeadersToClient($client, $forwardedHeaders);
        }
    }

    /**
     * Retrieve headers to forward from current request
     *
     * @param HeaderBag $requestHeaders
     * @param array     $headers
     * @param array     $forwardedHeaders
     */
    protected function retrieveHeaders(HeaderBag $requestHeaders, $headers, &$forwardedHeaders)
    {
        foreach ($headers as $header) {
            if ($requestHeaders->has($header)) {
                $forwardedHeaders[] = [
                    'name' => $header,
                    'value' => $requestHeaders->get($header),
                ];
            }
        }
    }

    /**
     * Retrieve headers to forward from current request query params
     *
     * @param array  $queries
     * @param array  $queriesToHeaders
     * @param array  $forwardedHeaders
     */
    protected function retrieveHeadersFromQueries($queries, $queriesToHeaders, &$forwardedHeaders)
    {
        if (count($queries) > 0) {
            foreach ($queriesToHeaders as $header => $queriesToHeader) {
                $currentQueryFromHeader = array_intersect_key($queries, array_flip($queriesToHeader));
                $forwardedHeaders[] = [
                    'name' => str_replace('_', '-', $header),
                    'value' => array_shift($currentQueryFromHeader),
                ];
            }
        }
    }

    /**
     * Forward headers to each client
     *
     * @param array $client
     * @param array $forwardedHeaders
     *
     * @return null
     */
    protected function forwardHeadersToClient($client, $forwardedHeaders)
    {
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
