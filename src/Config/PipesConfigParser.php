<?php

namespace Piper\Config;

use Piper\Container\Service;
use Piper\Container\ServiceMethodProxy;
use Piper\Container\Services;
use Piper\Pipeline\CallablePipe;
use Piper\Pipeline\ObjectTag;
use Piper\Pipeline\ObjectTags;
use Piper\Pipeline\Pipe;
use Piper\Pipeline\Util\StringUtil;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Webmozart\Assert\Assert;

/**
 * Pipes definitions configuration block parser.
 *
 * It expects following block structure:
 *
 * [
 *     'callable_pipe_id' => [
 *         'input' => ['class' => 'Some\Class', attributes => ['key' => 'value']]
 *         'trigger.callable' => function ($input) { return $output; },
 *         'pipeline' => ['pipeline_tag']
 *     ],
 *     'service_pipe_id' => [
 *         'input' => ['class' => 'Other\Class']
 *         'trigger.service' => ['service_id', 'serviceMethod'],
 *         'pipeline' => ['pipeline_tag']
 *     ],
 * ]
 *
 * As a result all pipes will be registered in container as service under given pipe id.
 *
 * Additionally given pipe services can be tagged with `pipeline:pipeline_tag`.
 */
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

    public function parse(ConfigBlock $configBlock): Services
    {
        $pipes = [];

        foreach ($configBlock->content() as $pipeId => $pipeConfig) {
            $pipes[] = $this->pipeDefinition($pipeConfig, $pipeId);
        }

        return new Services(...$pipes);
    }

    private function pipeDefinition(array $pipeConfig, string $pipeId): Service
    {
        return Service::fromInstance($pipeId, $this->buildPipe($pipeConfig))
            ->withSharing()
            ->withTags(...$this->configurePipelineTags($pipeConfig));
    }

    private function buildPipe(array $pipeConfig): CallablePipe
    {
        return new CallablePipe(
            $this->configureTrigger($pipeConfig),
            $this->configureInputTags($pipeConfig),
            $pipeConfig['order'] ?? Pipe::NORMAL
        );
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

            $serviceId = $def[0];
            $method = $def[1] ?? null;

            return new ServiceMethodProxy($this->container, $serviceId, $method);
        }

        throw new RuntimeException('Invalid pipe config: ' . var_export($config, true));
    }

    private function configureInputTags(array $config): ObjectTags
    {
        Assert::keyExists($config, 'input');
        Assert::keyExists($config['input'], 'class');

        $tag = new ObjectTag($config['input']['class'], $config['input']['attributes'] ?? []);

        return new ObjectTags($tag);
    }

    private function configurePipelineTags(array $config): array
    {
        if (!array_key_exists('pipelines', $config)) {
            return [];
        }

        Assert::allStringNotEmpty($config['pipelines']);

        return StringUtil::prependAll('pipeline:', ...$config['pipelines']);
    }
}
