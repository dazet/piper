<?php 

namespace spec\Piper\Http\Routing;

use PhpSpec\ObjectBehavior;

final class RouteSpec extends ObjectBehavior 
{
    function it_has_name_and_path()
    {
        $this->beConstructedWith('name', '/path');

        $this->name()->shouldReturn('name');
        $this->path()->shouldReturn('/path');
    }

    function it_can_match_static_path()
    {
        $this->beConstructedWith('name', '/static/path');

        $this->matchesPath('/static/path')->shouldReturn(true);
        $this->matchesPath('/static/path/further')->shouldReturn(false);
    }

    function it_can_match_variable_path()
    {
        $this->beConstructedWith('name', '/path/{param}');

        $this->matchesPath('/path/variable')->shouldReturn(true);
        $this->matchesPath('/path/variable/further')->shouldReturn(false);
    }

    function it_can_extract_params_from_variable_path()
    {
        $this->beConstructedWith('name', '/path/{a},{b}/{c}-{d}');

        $path = '/path/once,upon/a-time';
        $this->matchesPath($path)->shouldReturn(true);
        $this->extractParams($path)->shouldReturn(['a' => 'once', 'b' => 'upon', 'c' => 'a', 'd' => 'time']);
    }
}
