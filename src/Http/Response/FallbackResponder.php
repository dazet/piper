<?php

namespace Piper\Http\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class FallbackResponder
{
    public function notFound(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();

        $response = new Response();
        $response->getBody()->write(sprintf('Path %s not found', $uri->getPath()));

        return $response->withStatus(404);
    }
}
