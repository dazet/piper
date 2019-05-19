<?php

namespace Piper\Http;

use Piper\Config\ConfigBlockTagger;
use Piper\Config\ConfigFile;
use Piper\Config\ConfigParserPipe;
use Piper\Config\IterateConfigBlocks;
use Piper\Config\PipesConfigParser;
use Piper\Config\RegisterServices;
use Piper\Config\RoutesConfigParser;
use Piper\Config\ServicesConfigParser;
use Piper\Container\Container;
use Piper\Container\Service;
use Piper\Container\Services;
use Piper\Pipeline\ObjectTag\PipelineLogger;
use Piper\Pipeline\PipelineBuilder;
use Psr\Container\ContainerInterface;

class AppKernel implements ContainerInterface
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function loadConfig(ConfigFile ...$configs): void
    {
        $pipeline = $this->configPipelineBuilder()->forking()->build();

        foreach ($configs as $config) {
            $pipeline->pump($config);
        }
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id): bool
    {
        return $this->container->has($id);
    }

    protected function configPipelineBuilder(): PipelineBuilder
    {
        $logger = new PipelineLogger('config');
        $this->container->add(Service::fromInstance('logger.config', $logger));

        return PipelineBuilder::new()
            ->tagBy(new ConfigBlockTagger(), $logger)
            ->pipeFor(ConfigFile::class, new IterateConfigBlocks())
            ->pipeFor(Services::class, new RegisterServices($this->container))
            ->pipe(
                new ConfigParserPipe(new ServicesConfigParser($this->container)),
                new ConfigParserPipe(new PipesConfigParser($this->container)),
                new ConfigParserPipe(new RoutesConfigParser())
            );
    }
}
