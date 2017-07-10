<?php

namespace Piper\Http\Response;

use Piper\Http\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class ExampleAction
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return $this->buildResponse($request, 'Index.');
    }

    public function hello(ServerRequestInterface $request): ResponseInterface
    {
        return $this->buildResponse($request, 'Hello!');
    }

    private function buildResponse(ServerRequestInterface $request, string $message): Response
    {
        $uri = $request->getUri();
        $route = $request->getAttribute(Route::ATTRIBUTE, null);
        $routeName = $route instanceof Route ? $route->name() : 'unknown';

        $response = new Response();
        $response->getBody()->write("{$message}<br>Requested path: {$uri->getPath()}, route: {$routeName}");

        return $response->withStatus(200);
    }

}
