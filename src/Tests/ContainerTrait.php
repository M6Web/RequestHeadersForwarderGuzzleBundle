<?php

namespace M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\Tests;

use M6Web\Bundle\RequestHeadersForwarderGuzzleBundle\DependencyInjection\M6WebRequestHeadersForwarderGuzzleExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

trait ContainerTrait
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @param string $configType
     * @param bool   $debug
     */
    protected function initContainer($configType, $debug = false)
    {
        $this->container = new ContainerBuilder();
        $this->container->registerExtension(new M6WebRequestHeadersForwarderGuzzleExtension());

        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__.'/Fixtures/Resources/config/'));
        $loader->load(sprintf('%s.yml', $configType));

        $this->container->setParameter('kernel.debug', $debug);

        $this->container->compile();
    }
}
