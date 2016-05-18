<?php

namespace M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\Tests;

use M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\DependencyInjection\Configuration as TestedConfiguration;
use M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\DependencyInjection\M6WebRequestHeadersForwarderGuzzleExtension as TestedExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait ContainerTrait
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * Init container and load fixtures configuration file.
     *
     * @param string $configType
     * @param string $configFormat
     */
    protected function initContainer($configType, $configFormat = 'yml')
    {
        // Create container and register tested extension
        $this->container = new ContainerBuilder();
        $this->container->registerExtension(new TestedExtension());

        // Load configuration file
        switch ($configFormat) {
            case 'xml':
                $fileLoaderClass = '\Symfony\Component\DependencyInjection\Loader\XmlFileLoader';
                $fileExtension = 'xml';
                break;
            default:
                $fileLoaderClass = '\Symfony\Component\DependencyInjection\Loader\YamlFileLoader';
                $fileExtension = 'yml';
                break;
        }
        $loader = new $fileLoaderClass($this->container, new FileLocator(__DIR__.'/Fixtures/Resources/config/'));
        $loader->load(sprintf('%s.%s', $configType, $fileExtension));

        // Set $this->config from extension configuration
        $configs = $this->container->getExtensionConfig('m6_web_request_headers_forwarder_guzzle');
        $this->config = (new Processor())->processConfiguration(new TestedConfiguration(), $configs);

        // Compile container
        $this->container->setParameter('kernel.debug', true);
        $this->container->compile();
    }
}
