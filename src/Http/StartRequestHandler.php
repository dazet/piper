<?php

namespace Piper\Http;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

final class StartRequestHandler
{
    public function __invoke(StartRequest $command): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals();
    }
}
