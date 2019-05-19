<?php

namespace Piper\Config;

use Piper\Container\Service;
use Piper\Container\Services;
use Piper\Http\Routing\Route;
use Webmozart\Assert\Assert;

/**
 * Parses `routes` configuration block:
 *
 * $configFile = [
 *     'routes' => [
 *         'route_name' => ['path' => '/'],
 *         ...
 *     ]
 * ]
 *
 * Each route will be registered as a service with id `route.route_name` and tagged with `http.route`.
 */
final class RoutesConfigParser implements ConfigParser
{
    public const KEY = 'routes';
    public const TAG = 'http.route';

    public function key(): string
    {
        return self::KEY;
    }

    public function parse(ConfigBlock $configBlock): Services
    {
        $routes = [];

        foreach ($configBlock->content() as $routeName => $routeConfig) {
            $routes[] = $this->parseRoute($routeConfig, $routeName);
        }

        return new Services(...$routes);
    }

    private function parseRoute(array $routeConfig, string $name): Service
    {
        Assert::keyExists($routeConfig, 'path');
        Assert::string($routeConfig['path']);

        return Service::fromInstance("route.{$name}", new Route($name, $routeConfig['path']))->withTags(self::TAG);
    }
}
