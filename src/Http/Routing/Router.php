<?php

namespace Piper\Http\Routing;

use Psr\Http\Message\ServerRequestInterface;

final class Router
{
    /** @var Route[] */
    private $routes;

    public function __construct(Route ...$routes)
    {
        $this->routes = $routes;
    }
    
    public function routeRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        foreach ($this->routes as $route) {
            $request = $route->applyTo($request);

            if ($request->getAttribute(Route::ATTRIBUTE, null) !== null) {
                break;
            }
        }

        return $request;
    }
}
