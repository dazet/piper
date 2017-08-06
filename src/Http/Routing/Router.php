<?php

namespace Piper\Http\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

final class Router
{
    /** @var RouteBranch */
    private $routesTree;

    public function __construct(array $routes)
    {
        Assert::allIsInstanceOf($routes, Route::class);
        $this->routesTree = RouteBranch::root();

        foreach ($routes as $route) {
            $fragments = PathFragment::splitPath($route->path());
            $this->routesTree->addRoute($route, ...$fragments);
        }
    }

    public function routeRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $route = $this->findRoute($path);

        if ($route !== null) {
            return $this->requestWithAttributes($request, [Route::ATTRIBUTE => $route] + $route->extractParams($path));
        }

        return $request;
    }

    private function findRoute(string $path): ?Route
    {
        return $this->routesTree->getRoute(...PathFragment::splitPath($path));
    }

    private function requestWithAttributes(ServerRequestInterface $request, array $attributes): ServerRequestInterface
    {
        foreach ($attributes as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $request;
    }
}
