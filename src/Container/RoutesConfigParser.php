<?php

namespace Piper\Container;

use Piper\Http\Routing\Route;
use Webmozart\Assert\Assert;

final class RoutesConfigParser
{
    public function parseConfig(array $config): Route
    {
        Assert::keyExists($config, 'name');
        Assert::keyExists($config, 'path');

        return new Route($config['name'], $config['path']);
    }

    public function __invoke(array $config): Route
    {
        return $this->parseConfig($config);
    }
}
