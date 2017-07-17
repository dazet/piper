<?php

namespace Piper;

use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

return [
    'services' => [
        EmitterInterface::class => SapiEmitter::class,
        Http\StartRequestHandler::class => [],
        Http\Response\ExampleAction::class => [],
        Http\Response\FallbackResponder::class => [],
        Http\Response\ResponseEmitter::class => [
            'arguments' => [EmitterInterface::class],
        ],
        SapiEmitter::class => [],
        Http\Routing\Router::class => ['arguments' => ['route']],
    ],
    'routes' => [
        'index' => ['path' => '/', 'pipelines' => ['http']],
        'hello' => ['path' => '/hello', 'pipelines' => ['http']],
    ],
    'pipes' => [
        [
            'input' => ['class' => Http\StartRequest::class],
            'trigger.service' => [Http\StartRequestHandler::class],
            'pipelines' => ['http'],
        ],
        [
            'input' => ['class' => ServerRequestInterface::class],
            'trigger.service' => [Http\Routing\Router::class, 'routeRequest'],
            'pipelines' => ['http'],
        ],
        [
            'input' => [
                'class' => ServerRequestInterface::class,
                'attributes' => ['route' => 'index'],
            ],
            'trigger.service' => [Http\Response\ExampleAction::class, 'index'],
            'order' => Pipeline::NORMAL,
            'pipelines' => ['http'],
        ],
        [
            'input' => [
                'class' => ServerRequestInterface::class,
                'attributes' => ['route' => 'hello'],
            ],
            'trigger.service' => [Http\Response\ExampleAction::class, 'hello'],
            'order' => Pipeline::NORMAL,
            'pipelines' => ['http'],
        ],
        [
            'input' => ['class' => ServerRequestInterface::class],
            'trigger.service' => [Http\Response\FallbackResponder::class, 'notFound'],
            'order' => Pipeline::END,
            'pipelines' => ['http'],
        ],
        [
            'input' => ['class' => ResponseInterface::class],
            'trigger.service' => [Http\Response\ResponseEmitter::class, 'emit'],
            'order' => Pipeline::END,
            'pipelines' => ['http'],
        ],
    ]
];
