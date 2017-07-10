<?php

namespace Piper;

use Piper\Config\ConfigFile;
use Piper\Config\ConfigLoader;
use Piper\Config\PipesConfigParser;
use Piper\Config\RoutesConfigParser;
use Piper\Config\ServicesConfigParser;
use Piper\Container\Container;
use Piper\Container\Service;
use Psr\Container\ContainerInterface;

class Kernel implements ContainerInterface
{
    /** @var Container */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function loadConfig(ConfigFile ...$configFiles): void
    {
        $loader = $this->buildConfigLoader();

        $this->container->add(Service::fromInstance(ConfigLoader::class, $loader));
        $this->container->addServices($loader->loadFiles(...$configFiles));
    }

    protected function buildConfigLoader(): ConfigLoader
    {
        return new ConfigLoader(
            new ServicesConfigParser($this->container),
            new PipesConfigParser($this->container),
            new RoutesConfigParser()
        );
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id): bool
    {
        return $this->container->has($id);
    }
}
