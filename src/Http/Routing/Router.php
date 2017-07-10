<?php

namespace Piper\Http\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

final class Router
{
    /** @var Route[] */
    private $routes;

    public function __construct(array $routes)
    {
        Assert::allIsInstanceOf($routes, Route::class);
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
