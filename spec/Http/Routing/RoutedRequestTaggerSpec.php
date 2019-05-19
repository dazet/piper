<?php 

namespace spec\Piper\Http\Routing;

use PhpSpec\ObjectBehavior;
use Piper\Http\Routing\Route;
use Piper\Pipeline\ObjectTag;
use Piper\Pipeline\ObjectTags;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;

final class RoutedRequestTaggerSpec extends ObjectBehavior 
{
    function it_creates_tag_for_routed_request()
    {
        $route = new Route('hello', '/hello');
        $request = new ServerRequest();
        $request = $request->withAttribute(Route::ATTRIBUTE, $route);

        $tags = new ObjectTags();

        $this->tagsFor($request, $tags)
            ->shouldBeLike(new ObjectTags(new ObjectTag(ServerRequestInterface::class, ['route' => 'hello'])));
    }

    function it_tags_request_with_null_route_when_request_has_no_route()
    {
        $request = new ServerRequest();
        $tags = new ObjectTags();

        $this->tagsFor($request, $tags)
            ->shouldBeLike($tags->withTag(new ObjectTag(ServerRequestInterface::class, [Route::ATTRIBUTE => null])));
    }

    function it_tags_request_with_null_route_when_route_attribute_is_not_Route_instance()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute(Route::ATTRIBUTE, 'something other');
        $tags = new ObjectTags();

        $this->tagsFor($request, $tags)
            ->shouldBeLike($tags->withTag(new ObjectTag(ServerRequestInterface::class, [Route::ATTRIBUTE => null])));
    }
}
