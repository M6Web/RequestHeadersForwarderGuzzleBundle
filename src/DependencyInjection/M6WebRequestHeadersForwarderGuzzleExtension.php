<?php

namespace M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\DependencyInjection;


use M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\EventListener\RequestHeadersForwarderListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class M6WebRequestHeadersForwarderGuzzleExtension extends Extension implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->processConfiguration(new Configuration(), $configs);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $clients = [];
        foreach ($config['clients'] as $clientServiceId => $clientConfig) {
            // Check if $clientServiceId is a Guzzle client service
            if (!$container->hasDefinition($clientServiceId) || !is_a($container->getParameterBag()->resolveValue($container->getDefinition($clientServiceId)->getClass()), 'GuzzleHttp\Client', true)) {
                throw new \InvalidArgumentException(sprintf('[%s] client is not a valid Guzzle client service.', $clientServiceId));
            }

            // Add client config to $clients
            $clients[] = [
                'instance' => new Reference($clientServiceId),
                'headers' => $clientConfig['headers'],
                'queries_to_headers' => $clientConfig['queries_to_headers'],
            ];
        }

        $container->setDefinition(
            'm6web.request_headers_forwarder_guzzle.listener.request_headers_forwarder',
            (new Definition(
                RequestHeadersForwarderListener::class,
                [$clients]
            ))->addTag('kernel.event_listener', ['event' => 'kernel.request'])
        );
    }
}
