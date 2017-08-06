<?php

namespace Piper\Config;

use Piper\Common\Arrays;
use Piper\Container\Service;
use Piper\Container\Services;
use Piper\Http\Routing\Route;
use Webmozart\Assert\Assert;

final class RoutesConfigParser implements ConfigParser
{
    public const KEY = 'routes';

    public function key(): string
    {
        return self::KEY;
    }

    public function parse(array $configBlock): Services
    {
        return new Services(...Arrays::mapWithKey([$this, 'parseRoute'], $configBlock));
    }

    private function parseRoute(array $routeConfig, string $name): Service
    {
        Assert::keyExists($routeConfig, 'path');

        return Service::fromInstance("route.{$name}", new Route($name, $routeConfig['path']))->withTags('route');
    }
}
