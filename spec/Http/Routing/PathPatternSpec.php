<?php

namespace spec\Piper\Http\Routing;

use PhpSpec\ObjectBehavior;

final class PathPatternSpec extends ObjectBehavior
{
    function it_matches_static_path()
    {
        $this->beConstructedWith('/static/path');

        $this->hasParams()->shouldReturn(false);

        $this->matches('/static/path')->shouldReturn(true);
        $this->matches('/other/path')->shouldReturn(false);
        $this->extractParams('/static/path')->shouldReturn([]);
    }

    function it_matches_path_with_param()
    {
        $this->beConstructedWith('/path/to/{param}');

        $this->hasParams()->shouldReturn(true);

        $this->extractParams('/path/to/surprise')->shouldReturn(['param' => 'surprise']);
    }

    function it_matches_path_with_multiple_params()
    {
        $this->beConstructedWith('/path/to/{param1}/and/{param2}');

        $this->hasParams()->shouldReturn(true);

        $path = '/path/to/surprise/and/fear';
        $this->matches($path)->shouldReturn(true);
        $this->matches('/')->shouldReturn(false);
        $this->extractParams($path)->shouldReturn(['param1' => 'surprise', 'param2' => 'fear']);
    }

    function it_allows_to_define_custom_param_pattern()
    {
        $this->beConstructedWith('/see/{number}/{letters}', ['number' => '\d+', 'letters' => '[a-z]+']);

        $this->matches('/see/1/a')->shouldReturn(true);
        $this->matches('/see/99999999/alphabet')->shouldReturn(true);

        $this->matches('/see/000a/abc')->shouldReturn(false);
        $this->matches('/see/000/abc1')->shouldReturn(false);
    }

    function it_allows_to_define_optional_parameter()
    {
        $this->beConstructedWith('/say{/what?}');

        $this->matches('/say/hello')->shouldReturn(true);
        $this->extractParams('/say/hello')->shouldReturn(['what' => 'hello']);
    }

    function it_extracts_null_when_optional_parameter_is_not_defined()
    {
        $this->beConstructedWith('/say{/what?}');

        $this->matches('/say')->shouldReturn(true);
        $this->extractParams('/say')->shouldReturn(['what' => null]);
    }

    function it_matches_empty_string_when_is_optional()
    {
        $this->beConstructedWith('{/param?}');

        $this->matches('')->shouldReturn(true);
    }
}
