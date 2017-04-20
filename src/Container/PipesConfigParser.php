<?php

namespace Piper\Container;

use Piper\Pipe;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class PipesConfigParser
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function parseConfig(array $config): Pipe
    {
        return new Pipe\CallablePipe(
            $this->configureTrigger($config),
            $this->configureInputTags($config),
            $config['order'] ?? Pipe::NORMAL
        );
    }

    public function __invoke(array $config): Pipe
    {
        return $this->parseConfig($config);
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
}
