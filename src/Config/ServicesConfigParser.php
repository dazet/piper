<?php

namespace Piper\Config;

use Closure;
use Piper\Container\Service;
use Piper\Container\Services;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Webmozart\Assert\Assert;
use function array_map;
use function is_array;
use function is_string;

/**
 * Parses `services` configuration block:
 *
 * $configFile = [
 *     'services' => [
 *         # Service created by callback
 *         'service_1' => function() {
 *             return new ExampleService();
 *         },
 *
 *         # Service alias
 *         'service_alias' => 'service_1',
 *
 *         # Service created by new class instance without arguments
 *         Some\ExampleService::class => [],
 *         'service_2' => [
 *             'class' => Some\ExampleService::class,
 *         ],
 *
 *         # Same as above with arguments
 *         Some\ExampleService::class => [
 *             'arguments' => ['service_1'],
 *         ],
 *         'service_2' => [
 *             'class' => Some\ExampleService::class,
 *             'arguments' => ['service_1'],
 *         ],
 *
 *         # Service with tags
 *         Some\ExampleService::class => [
 *             'arguments' => ['service_1'],
 *             'tags' => ['tag1', 'tag2'],
 *         ],
 *         ...
 *     ]
 * ]
 *
 * Each route will be registered as a service with id `route.route_name` and tagged with `http.route`.
 */
final class ServicesConfigParser implements ConfigParser
{
    public const KEY = 'services';

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
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
        $services = [];

        foreach ($configBlock->content() as $serviceId => $definition) {
            $services[] = $this->buildService($definition, $serviceId);
        }

        return new Services(...$services);
    }

    private function buildService($definition, string $serviceId): Service
    {
        if ($definition instanceof Closure) {
            return Service::fromFactory($serviceId, $definition);
        }

        if (is_string($definition)) {
            return $this->buildServiceAlias($serviceId, $definition);
        }

        if (is_array($definition)) {
            return $this->buildServiceFromClass($serviceId, $definition);
        }

        throw new RuntimeException("Invalid definition for {$serviceId}");
    }

    private function buildServiceAlias(string $serviceId, string $definition): Service
    {
        return Service::fromFactory($serviceId, function() use ($definition) {
            return $this->container->get($definition);
        });
    }

    private function buildServiceFromClass(string $serviceId, array $definition): Service
    {
        $class = $definition['class'] ?? $serviceId;
        $arguments = $definition['arguments'] ?? [];
        $shared = (bool)($definition['shared'] ?? true);
        $tags = $definition['tags'] ?? [];
        $method = $definition['method'] ?? '__construct';

        Assert::stringNotEmpty($class);
        Assert::isArray($arguments);
        Assert::isArray($tags);
        Assert::allString($tags);

        $factory = $method === '__construct'
            ? function() use ($class, $arguments) {
                return new $class(...array_map([$this->container, 'get'], $arguments));
            }
            : function() use ($class, $method, $arguments) {
                return $class::$method(...array_map([$this->container, 'get'], $arguments));
            };

        return Service::fromFactory($serviceId, $factory)->withSharing($shared)->withTags(...$tags);
    }
}
