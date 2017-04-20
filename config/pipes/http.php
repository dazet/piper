<?php

namespace Piper;

return [
    [
        'input'           => ['class' => 'Piper\Http\StartRequest'],
        'trigger.service' => ['Piper\Http\StartRequestHandler'],
    ],
    [
        'input'           => ['class' => 'Psr\Http\Message\ServerRequestInterface'],
        'trigger.service' => ['Piper\Http\Routing\Router', 'routeRequest'],
    ],
    [
        'input'           => ['class' => 'Psr\Http\Message\ServerRequestInterface'],
        'trigger.service' => ['Piper\Http\Response\FallbackResponder', 'notFound'],
        'order'           => Pipe::END
    ],
    [
        'input'           => ['class' => 'Psr\Http\Message\ResponseInterface'],
        'trigger.service' => ['Piper\Http\Response\ResponseEmitter', 'emit'],
        'order'           => Pipe::END
    ],
];
