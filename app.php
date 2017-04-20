<?php

require 'vendor/autoload.php';

use League\Container\Container;
use League\Container\ReflectionContainer;
use Piper\Http\StartRequest;
use Piper\Pipe\ObjectTagger;
use Piper\Pipeline;
use Piper\Container\PipesConfigParser;

$servicesConfig = require __DIR__ . '/config/services.php';
$pipesConfig = require __DIR__ . '/config/pipes.php';

$container = new Container();
$container->delegate(new ReflectionContainer());

foreach ($servicesConfig as $serviceId => $definition) {
    $container->add($serviceId, $definition);
}

$factory = new PipesConfigParser($container);
$pipes = array_map($factory, $pipesConfig);
$pipeline = new Pipeline($container->get(ObjectTagger::class), ...$pipes);

$pipeline->pump(new StartRequest());
