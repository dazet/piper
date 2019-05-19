<?php

namespace spec\Piper\Pipeline;

use Generator;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use spec\Piper\Stub\A;
use spec\Piper\Stub\AFunction;
use spec\Piper\Stub\AInterface;
use spec\Piper\Stub\B;
use spec\Piper\Stub\BFunction;
use spec\Piper\Stub\C;
use spec\Piper\Stub\Callback;
use spec\Piper\Stub\PassNext;

final class PipelineBuilderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('new');
    }

    function it_can_build_pipeline_from_closures()
    {
        $c = new C();

        $pipeline = $this
            ->pipe(
                function (A $a): B {
                    return new B();
                },
                function (B $b) use ($c): C {
                    return $c;
                }
            )
            ->build();

        $pipeline->pump(new A())->shouldReturn($c);
    }

    function it_can_build_pipeline_from_closures_step_by_step()
    {
        $c = new C();

        $pipeline = $this
            ->pipe(function (A $a): B {
                return new B();
            })
            ->pipe(function (B $b) use ($c): C {
                return $c;
            })
            ->build();

        $pipeline->pump(new A())->shouldReturn($c);
    }

    function it_can_build_pipeline_from_closures_with_custom_defined_input_types()
    {
        $c = new C();

        $pipeline = $this
            ->pipeFor(A::class, function ($a): B {
                return new B();
            })
            ->pipeFor(B::class, function ($b) use ($c): C {
                return $c;
            })
            ->build();

        $pipeline->pump(new A())->shouldReturn($c);
    }

    function it_can_resolve_closure_input_by_interface()
    {
        $c = new C();

        $pipeline = $this
            ->pipe(
                function (AInterface $a): B {
                    return new B();
                },
                function (B $b) use ($c): C {
                    return $c;
                }
            )
            ->build();

        $pipeline->pump(new A())->shouldReturn($c);
    }

    function it_can_build_pipeline_from_callable_objects()
    {
        $c = new C();

        $pipeline = $this->pipe(new AFunction(new B()), new BFunction($c))->build();

        $pipeline->pump(new A())->shouldReturn($c);
    }

    function it_can_build_pipeline_from_callable_objects_with_custom_defined_input_types()
    {
        $c = new C();

        $pipeline = $this
            ->pipeFor(A::class, new PassNext(new B()))
            ->pipeFor(B::class, new PassNext($c))
            ->build();

        $pipeline->pump(new A())->shouldReturn($c);
    }

    function it_throws_InvalidArgumentException_when_cannot_resolve_closure_input()
    {
        $fn = function($a): B {
            return new B();
        };

        $this->shouldThrow(InvalidArgumentException::class)->during('pipe', [$fn]);
    }

    function it_allows_to_create_forking_pipeline_when_some_pipe_returns_generator(
        Callback $processB,
        Callback $processC
    ) {
        $b = new B();
        $c = new C();

        $pipeline = $this
            ->pipe(function(A $a) use ($b, $c): Generator {
                yield $b;
                yield $c;
            })
            ->pipeFor(B::class, $processB)
            ->pipeFor(C::class, $processC)
            ->forking()
            ->build();

        $pipeline->pump(new A());

        $processB->__invoke($b)->shouldHaveBeenCalled();
        $processC->__invoke($c)->shouldHaveBeenCalled();
    }
}
