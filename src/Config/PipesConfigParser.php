<?php

namespace Piper\Config;

use Piper\Container\Service;
use Piper\Container\ServiceMethodProxy;
use Piper\Container\Services;
use Piper\Pipe;
use Piper\Pipeline;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class PipesConfigParser implements ConfigParser
{
    public const KEY = 'pipes';

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function key(): string
    {
        return self::KEY;
    }

    /**
     * @param array[] $configBlock
     */
    public function parse(array $configBlock): Services
    {
        $definitions = [];

        foreach ($configBlock as $key => $config) {
            $definitions[] = $this->pipeDefinition($key, $config);
        }

        return new Services(...$definitions);
    }

    private function pipeDefinition(string $key, array $config): Service
    {
        return Service::fromInstance($key, new Pipe\CallablePipe(
                $this->configureTrigger($config),
                $this->configureInputTags($config),
                $config['order'] ?? Pipeline::NORMAL
            ))
            ->withSharing()
            ->withTags(...$this->configurePipelineTags($config));
    }

    private function configureTrigger(array $config): callable
    {
        if (!empty($config['trigger.callable'])) {
            Assert::isCallable($config['trigger.callable']);

            return $config['trigger.callable'];
        }

        if (!empty($config['trigger.service'])) {
            $def = array_values($config['trigger.service']);

            Assert::keyExists($def, 0);

            $class = $def[0];
            $method = $def[1] ?? null;

            return new ServiceMethodProxy($this->container, $class, $method);
        }

        throw new \RuntimeException('Invalid pipe config: ' . var_export($config, true));
    }

    private function configureInputTags(array $config): Pipe\ObjectTags
    {
        Assert::keyExists($config, 'input');
        Assert::keyExists($config['input'], 'class');

        $tag = new Pipe\ObjectTag($config['input']['class'], $config['input']['attributes'] ?? []);

        return new Pipe\ObjectTags($tag);
    }

    private function configurePipelineTags(array $config): array
    {
        if (!array_key_exists('pipelines', $config)) {
            return [];
        }

        Assert::allStringNotEmpty($config['pipelines']);

        return array_map(
            function(string $pipeline): string {
                return "pipeline:{$pipeline}";
            },
            $config['pipelines']
        );
    }
}
