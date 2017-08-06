<?php 

namespace spec\Piper\Http\Routing;

use PhpSpec\ObjectBehavior;
use Piper\Http\Routing\PathFragment;

final class PathFragmentSpec extends ObjectBehavior 
{
    function it_can_wrap_static_path_fragment()
    {
        $this->beConstructedThrough('create', ['static']);

        $this->isVariable()->shouldReturn(false);
        $this->matches(PathFragment::create('static'))->shouldReturn(true);
        $this->matches(PathFragment::create('other'))->shouldReturn(false);
        $this->toString()->shouldReturn('static');
    }

    function it_can_wrap_variable_path_fragment()
    {
        $this->beConstructedThrough('create', ['{param1},{param2}']);

        $this->isVariable()->shouldReturn(true);
        $this->matches(PathFragment::create('a,b'))->shouldReturn(true);
        $this->matches(PathFragment::create('a'))->shouldReturn(false);
        $this->toString()->shouldReturn('{param1},{param2}');
    }

    function it_has_method_to_split_path_into_fragments()
    {
        $this->beConstructedThrough('splitPath', ['/long/static/path']);

        $this[0]->shouldBeLike(PathFragment::create('long'));
        $this[1]->shouldBeLike(PathFragment::create('static'));
        $this[2]->shouldBeLike(PathFragment::create('path'));
    }

    function it_splits_path_with_optional_fragments()
    {
        $this->beConstructedThrough('splitPath', ['/path{/maybe?}/right']);

        $this[0]->shouldBeLike(PathFragment::create('path'));
        $this[1]->shouldBeLike(PathFragment::create('{maybe?}'));
        $this[2]->shouldBeLike(PathFragment::create('right'));
    }
}
