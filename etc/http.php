<?php

namespace Piper;

return [
    'services' => [
        'Zend\Diactoros\Response\EmitterInterface' => 'Zend\Diactoros\Response\SapiEmitter',
        'Piper\Http\StartRequestHandler' => [],
        'Piper\Http\Response\ExampleAction' => [],
        'Piper\Http\Response\FallbackResponder' => [],
        'Piper\Http\Response\ResponseEmitter' => [
            'arguments' => ['Zend\Diactoros\Response\EmitterInterface'],
        ],
        'Zend\Diactoros\Response\SapiEmitter' => [],
        'Piper\Http\Routing\Router' => [
            'arguments' => ['route'],
        ],
    ],
    'routes' => [
        'index' => ['path' => '/', 'pipelines' => ['http']],
        'hello' => ['path' => '/hello', 'pipelines' => ['http']],
    ],
    'pipes' => [
        'StartRequestHandler' => [
            'input' => ['class' => 'Piper\Http\StartRequest'],
            'trigger.service' => ['Piper\Http\StartRequestHandler'],
            'pipelines' => ['http'],
        ],
        'Router' => [
            'input' => ['class' => 'Psr\Http\Message\ServerRequestInterface'],
            'trigger.service' => ['Piper\Http\Routing\Router', 'routeRequest'],
            'pipelines' => ['http'],
        ],
        'ExampleIndexAction' => [
            'input' => [
                'class' => 'Psr\Http\Message\ServerRequestInterface',
                'attributes' => ['route' => 'index'],
            ],
            'trigger.service' => ['Piper\Http\Response\ExampleAction', 'index'],
            'order' => Pipeline::NORMAL,
            'pipelines' => ['http'],
        ],
        'ExampleHelloAction' => [
            'input' => [
                'class' => 'Psr\Http\Message\ServerRequestInterface',
                'attributes' => ['route' => 'hello'],
            ],
            'trigger.service' => ['Piper\Http\Response\ExampleAction', 'hello'],
            'order' => Pipeline::NORMAL,
            'pipelines' => ['http'],
        ],
        'FallbackResponder' => [
            'input' => ['class' => 'Psr\Http\Message\ServerRequestInterface'],
            'trigger.service' => ['Piper\Http\Response\FallbackResponder', 'notFound'],
            'order' => Pipeline::END,
            'pipelines' => ['http'],
        ],
        'ResponseEmitter' => [
            'input' => ['class' => 'Psr\Http\Message\ResponseInterface'],
            'trigger.service' => ['Piper\Http\Response\ResponseEmitter', 'emit'],
            'order' => Pipeline::END,
            'pipelines' => ['http'],
        ],
    ]
];
