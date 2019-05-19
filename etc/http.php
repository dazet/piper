<?php

namespace Piper;

use Piper\Http\Routing\RoutedRequestTagger;
use Piper\Pipeline\ObjectTag\PipelineLogger;
use Piper\Pipeline\ObjectTag\TaggersAggregate;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;

return [
    'services' => [
        'logger.http' => function (): PipelineLogger {
            return new PipelineLogger('http');
        },
        RoutedRequestTagger::class => [],
        'http.tagger' => [
            'class' => TaggersAggregate::class,
            'method' => 'default',
            'arguments' => [RoutedRequestTagger::class, 'logger.http'],
        ],
        EmitterInterface::class => SapiEmitter::class,
        Http\StartRequestHandler::class => [],
        Http\Response\ExampleAction::class => [],
        Http\Response\FallbackResponder::class => [],
        Http\Response\ResponseEmitter::class => [
            'arguments' => [EmitterInterface::class, ContainerInterface::class],
        ],
        SapiEmitter::class => [],
        Http\Routing\Router::class => ['arguments' => ['http.route']],
    ],
    'routes' => [
        'index' => ['path' => '/'],
        'hello' => ['path' => '/hello'],
    ],
    'pipes' => [
        'StartRequestHandler' => [
            'input' => ['class' => Http\StartRequest::class],
            'trigger.service' => [Http\StartRequestHandler::class],
            'pipelines' => ['http'],
        ],
        'Router' => [
            'input' => [
                'class' => ServerRequestInterface::class,
                'attributes' => ['route' => null],
            ],
            'trigger.service' => [Http\Routing\Router::class, 'routeRequest'],
            'pipelines' => ['http'],
        ],
        'IndexAction' => [
            'input' => [
                'class' => ServerRequestInterface::class,
                'attributes' => ['route' => 'index'],
            ],
            'trigger.service' => [Http\Response\ExampleAction::class, 'index'],
            'order' => Pipeline\Pipe::NORMAL,
            'pipelines' => ['http'],
        ],
        'HelloAction' => [
            'input' => [
                'class' => ServerRequestInterface::class,
                'attributes' => ['route' => 'hello'],
            ],
            'trigger.service' => [Http\Response\ExampleAction::class, 'hello'],
            'order' => Pipeline\Pipe::NORMAL,
            'pipelines' => ['http'],
        ],
        'NotFoundAction' => [
            'input' => ['class' => ServerRequestInterface::class],
            'trigger.service' => [Http\Response\FallbackResponder::class, 'notFound'],
            'order' => Pipeline\Pipe::END,
            'pipelines' => ['http'],
        ],
        'ResponseEmitter' => [
            'input' => ['class' => ResponseInterface::class],
            'trigger.service' => [Http\Response\ResponseEmitter::class, 'emit'],
            'order' => Pipeline\Pipe::END,
            'pipelines' => ['http'],
        ],
    ],
];
