<?php

use Piper\Container\RoutesConfigParser;
use Piper\Http\Routing\RoutedRequestTagger;
use Piper\Http\Routing\Router;
use Piper\Pipe\ObjectTagger\ClassTagger;
use Piper\Pipe\ObjectTagger\InterfacesTagger;
use Piper\Pipe\ObjectTagger\TaggersAggregate;

return [
    'Psr\Container\ContainerInterface' => 'League\Container\Container',
    'Piper\Pipe\ObjectTagger' => function() {
        return new TaggersAggregate(
            new ClassTagger(), new InterfacesTagger(), new RoutedRequestTagger()
        );
    },
    'Piper\Http\Routing\Router' => function () {
        $routes = array_map(new RoutesConfigParser(), require __DIR__ . '/routes.php');

        return new Router(...$routes);
    },
    'Zend\Diactoros\Response\EmitterInterface' => 'Zend\Diactoros\Response\SapiEmitter',
];
