<?php 

namespace spec\Piper;

use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Piper\Pipe;
use Piper\Pipeline;
use Prophecy\Argument;
use spec\Piper\Stub;

final class PipelineSpec extends ObjectBehavior 
{
    function it_executes_pipeline_until_there_is_not_pipe_for_the_result(
        Pipe $pipe1,
        Pipe $pipe2,
        Stub\Callback $trigger1,
        Stub\Callback $trigger2,
        Stub\Callback $restHandler
    ) {
        $this->constructClassTaggerPipeline($pipe1, $pipe2);

        $a = new Stub\A();
        $b = new Stub\B();
        $c = new Stub\C();

        $this->mockPipe($pipe1, $trigger1, $this->tagsForClass(Stub\A::class));
        $trigger1->__invoke($a)->shouldBeCalledTimes(1)->willReturn($b);

        $this->mockPipe($pipe2, $trigger2, $this->tagsForClass(Stub\B::class));
        $trigger2->__invoke($b)->shouldBeCalledTimes(1)->willReturn($c);

        $restHandler->__invoke($c)->shouldBeCalledTimes(1);

        $this->pump($a, $restHandler);
    }

    function it_does_not_pump_second_pipe_with_the_same_input_when_first_pipe_redirects_flow(
        Pipe $pipe1,
        Pipe $pipe2,
        Stub\Callback $trigger1,
        Stub\Callback $trigger2,
        Stub\Callback $restHandler
    ) {
        $this->constructClassTaggerPipeline($pipe1, $pipe2);

        $a = new Stub\A();
        $b = new Stub\B();

        $this->mockPipe($pipe1, $trigger1, $this->tagsForClass(Stub\A::class));
        $trigger1->__invoke($a)->shouldBeCalledTimes(1)->willReturn($b);

        $this->mockPipe($pipe2, $trigger2, $this->tagsForClass(Stub\A::class));
        $trigger2->__invoke(Argument::any())->shouldNotBeCalled();

        $restHandler->__invoke($b)->shouldBeCalledTimes(1);

        $this->pump($a, $restHandler);
    }

    function it_continues_with_next_pipe_for_the_same_input_when_first_pipe_has_modified_input(
        Pipe $pipe1,
        Pipe $pipe2,
        Stub\Callback $trigger1,
        Stub\Callback $trigger2,
        Stub\Callback $restHandler
    ) {
        $this->constructClassTaggerPipeline($pipe1, $pipe2);

        $a1 = new Stub\A('A1');
        $a2 = new Stub\A('A2');

        $this->mockPipe($pipe1, $trigger1, $this->tagsForClass(Stub\A::class));
        $trigger1->__invoke($a1)->shouldBeCalledTimes(1)->willReturn($a2);

        $this->mockPipe($pipe2, $trigger2, $this->tagsForClass(Stub\A::class));
        $trigger2->__invoke($a2)->shouldBeCalledTimes(1)->willReturn(null);

        $restHandler->__invoke($a2)->shouldBeCalledTimes(1);

        $this->pump($a1, $restHandler);
    }

    function it_connects_pipes_in_defined_order(
        Pipe $pipe1,
        Pipe $pipe2,
        Pipe $pipe3,
        Pipe $pipe4,
        Pipe $pipe5,
        Stub\Callback $trigger1,
        Stub\Callback $trigger2,
        Stub\Callback $trigger3,
        Stub\Callback $trigger4,
        Stub\Callback $trigger5,
        Stub\Callback $restHandler
    ) {
        $this->constructClassTaggerPipeline($pipe1, $pipe2, $pipe3, $pipe4, $pipe5);

        $a1 = new Stub\A('A1');
        $a2 = new Stub\A('A2');
        $a3 = new Stub\A('A3');
        $a4 = new Stub\A('A4');
        $a5 = new Stub\A('A5');
        $a6 = new Stub\A('A6');

        $this->mockPipe($pipe1, $trigger1, $this->tagsForClass(Stub\A::class), Pipeline::START);
        $this->mockPipe($pipe2, $trigger2, $this->tagsForClass(Stub\A::class), Pipeline::BEFORE);
        $this->mockPipe($pipe3, $trigger3, $this->tagsForClass(Stub\A::class), Pipeline::NORMAL);
        $this->mockPipe($pipe4, $trigger4, $this->tagsForClass(Stub\A::class), Pipeline::AFTER);
        $this->mockPipe($pipe5, $trigger5, $this->tagsForClass(Stub\A::class), Pipeline::END);

        $trigger1->__invoke($a1)->shouldBeCalledTimes(1)->willReturn($a2);
        $trigger2->__invoke($a2)->shouldBeCalledTimes(1)->willReturn($a3);
        $trigger3->__invoke($a3)->shouldBeCalledTimes(1)->willReturn($a4);
        $trigger4->__invoke($a4)->shouldBeCalledTimes(1)->willReturn($a5);
        $trigger5->__invoke($a5)->shouldBeCalledTimes(1)->willReturn($a6);

        $restHandler->__invoke($a6)->shouldBeCalledTimes(1);

        $this->pump($a1, $restHandler);
    }

    function it_forks_when_some_pipe_yields_values(Stub\Callback $restHandler)
    {
        $a = new Stub\A();
        $b = new Stub\B();
        $c = new Stub\C();

        $pipe = new Pipe\CallablePipe(
            function(Stub\A $a) use ($b, $c): \Generator {
                yield $b;
                yield $c;
            },
            Pipe\ObjectTags::fromClass(Stub\A::class)
        );

        $this->constructClassTaggerPipeline($pipe);

        $this->pump($a, $restHandler);

        $restHandler->__invoke($b)->shouldHaveBeenCalledTimes(1);
        $restHandler->__invoke($c)->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @param Pipe[]|Collaborator[] $pipes
     */
    private function constructClassTaggerPipeline(...$pipes): void
    {
        $this->beConstructedWith(new Pipe\ObjectTagger\ClassTagger(), ...$pipes);
    }

    private function tagsForClass(string ...$classes): Pipe\ObjectTags
    {
        return Pipe\ObjectTags::fromClasses(...$classes);
    }

    /**
     * @param Pipe|Collaborator $pipe
     * @param callable|Collaborator $trigger
     * @param int $order
     */
    private function mockPipe($pipe, $trigger, Pipe\ObjectTags $tags, int $order = Pipeline::NORMAL): void
    {
        $pipe->trigger()->willReturn($trigger);
        $pipe->input()->willReturn($tags);
        $pipe->order()->willReturn($order);
    }
}
