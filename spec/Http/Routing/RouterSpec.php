<?php 

namespace spec\Piper\Http\Routing;

use PhpSpec\ObjectBehavior;
use Piper\Http\Routing\Route;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class RouterSpec extends ObjectBehavior 
{
    function it_routes_request_to_the_right_route(ServerRequestInterface $request, UriInterface $uri)
    {
        $right = new Route('right', '/this/is/right');
        $wrong = new Route('wrong', '/this/is/wrong');

        $this->beConstructedWith([$wrong, $right]);

        $request->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/this/is/right');

        $request->withAttribute(Route::ATTRIBUTE, $right)->shouldBeCalled()->willReturn($request);

        $this->routeRequest($request)->shouldReturn($request);
    }
}
