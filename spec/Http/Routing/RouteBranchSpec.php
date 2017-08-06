<?php 

namespace spec\Piper\Http\Routing;

use PhpSpec\ObjectBehavior;
use Piper\Http\Routing\PathFragment;
use Piper\Http\Routing\Route;

final class RouteBranchSpec extends ObjectBehavior 
{
    function it_finds_a_route_for_static_path()
    {
        $this->beConstructedThrough('root');

        $path1 = '/path/to/first/route';
        $route1 = new Route('route_1', $path1);
        $path2 = '/second/route/path';
        $route2 = new Route('route_2', $path2);

        $fragments1 = PathFragment::splitPath($path1);
        $this->addRoute($route1, ...$fragments1);
        $fragments2 = PathFragment::splitPath($path2);
        $this->addRoute($route2, ...$fragments2);

        $this->getRoute(...$fragments1)->shouldReturn($route1);
        $this->getRoute(...$fragments2)->shouldReturn($route2);
        $this->getRoute(...PathFragment::splitPath('/another/path'))->shouldReturn(null);
    }

    function it_finds_a_route_for_variable_path()
    {
        $this->beConstructedThrough('root');

        $variablePath = '/page/{param}';
        $variableRoute = new Route('route_1', $variablePath);
        $staticPath = '/page/static';
        $staticRoute = new Route('route_2', $staticPath);

        $this->addRoute($variableRoute, ...PathFragment::splitPath($variablePath));
        $this->addRoute($staticRoute, ...PathFragment::splitPath($staticPath));

        $this->getRoute(...PathFragment::splitPath('/page/1'))->shouldReturn($variableRoute);
        $this->getRoute(...PathFragment::splitPath('/page/other'))->shouldReturn($variableRoute);
        $this->getRoute(...PathFragment::splitPath('/page/static'))->shouldReturn($staticRoute);

        $this->getRoute(...PathFragment::splitPath('/page/'))->shouldReturn(null);
        $this->getRoute(...PathFragment::splitPath('/page/x/other'))->shouldReturn(null);
    }

    function it_finds_a_route_for_a_path_with_empty_optional_param()
    {
        $this->beConstructedThrough('root');

        $path = '/page{/number?}';
        $route = new Route('page', $path);

        $this->addRoute($route, ...PathFragment::splitPath($path));

        $this->getRoute(...PathFragment::splitPath('/page'))->shouldReturn($route);
    }
}
