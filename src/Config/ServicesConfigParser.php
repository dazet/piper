<?php

namespace Piper\Config;

use Piper\Container\Service;
use Piper\Container\Services;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

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

    /**
     * @param mixed[] $configBlock [serviceId => definition]
     * @return Services
     * @throws \RuntimeException
     */
    public function parse(array $configBlock): Services
    {
        $services = [];

        foreach ($configBlock as $serviceId => $definition) {
            switch (true) {
                case ($definition instanceof \Closure):
                    $services[] = Service::fromFactory($serviceId, $definition);
                    break;

                case is_string($definition):
                    $services[] = $this->buildServiceAlias($serviceId, $definition);
                    break;

                case (is_array($definition)):
                    $services[] = $this->buildServiceFromClass($serviceId, $definition);
                    break;

                default:
                    throw new \RuntimeException("Invalid definition for {$serviceId}");
            }
        }

        return new Services(...$services);
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

        Assert::stringNotEmpty($class);
        Assert::isArray($arguments);
        Assert::allString($tags);

        return Service::fromFactory($serviceId, function() use ($class, $arguments) {
            return new $class(...array_map([$this->container, 'get'], $arguments));
        })
            ->withSharing($shared)
            ->withTags(...$tags);
    }
}