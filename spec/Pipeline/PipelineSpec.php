<?php 

namespace spec\Piper\Pipeline;

use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Piper\Pipeline\ObjectTag\ClassTagger;
use Piper\Pipeline\ObjectTags;
use Piper\Pipeline\Pipe;
use Prophecy\Argument;
use spec\Piper\Stub;

final class PipelineSpec extends ObjectBehavior 
{
    function it_executes_pipeline_until_there_is_not_pipe_for_the_result(
        Pipe $pipe1,
        Pipe $pipe2
    ) {
        $this->constructClassTaggerPipeline($pipe1, $pipe2);

        $a = new Stub\A();
        $b = new Stub\B();
        $c = new Stub\C();

        $this->mockPipe($pipe1, $this->tagsForClass(Stub\A::class));
        $pipe1->__invoke($a)->shouldBeCalledTimes(1)->willReturn($b);

        $this->mockPipe($pipe2, $this->tagsForClass(Stub\B::class));
        $pipe2->__invoke($b)->shouldBeCalledTimes(1)->willReturn($c);

        $this->pump($a)->shouldReturn($c);
    }

    function it_does_not_pump_second_pipe_with_the_same_input_when_first_pipe_redirects_flow(
        Pipe $pipe1,
        Pipe $pipe2
    ) {
        $this->constructClassTaggerPipeline($pipe1, $pipe2);

        $a = new Stub\A();
        $b = new Stub\B();

        $this->mockPipe($pipe1, $this->tagsForClass(Stub\A::class));
        $pipe1->__invoke($a)->shouldBeCalledTimes(1)->willReturn($b);

        $this->mockPipe($pipe2, $this->tagsForClass(Stub\A::class));
        $pipe2->__invoke(Argument::any())->shouldNotBeCalled();

        $this->pump($a)->shouldReturn($b);
    }

    function it_continues_with_next_pipe_for_the_same_input_when_first_pipe_has_modified_input(
        Pipe $pipe1,
        Pipe $pipe2
    ) {
        $this->constructClassTaggerPipeline($pipe1, $pipe2);

        $a1 = new Stub\A('A1');
        $a2 = new Stub\A('A2');

        $this->mockPipe($pipe1, $this->tagsForClass(Stub\A::class));
        $pipe1->__invoke($a1)->shouldBeCalledTimes(1)->willReturn($a2);

        $this->mockPipe($pipe2, $this->tagsForClass(Stub\A::class));
        $pipe2->__invoke($a2)->shouldBeCalledTimes(1)->willReturn(null);

        $this->pump($a1)->shouldReturn($a2);
    }

    function it_connects_pipes_in_defined_order(
        Pipe $pipe1,
        Pipe $pipe2,
        Pipe $pipe3,
        Pipe $pipe4,
        Pipe $pipe5
    ) {
        $this->constructClassTaggerPipeline($pipe1, $pipe2, $pipe3, $pipe4, $pipe5);

        $a1 = new Stub\A('A1');
        $a2 = new Stub\A('A2');
        $a3 = new Stub\A('A3');
        $a4 = new Stub\A('A4');
        $a5 = new Stub\A('A5');
        $a6 = new Stub\A('A6');

        $this->mockPipe($pipe1, $this->tagsForClass(Stub\A::class), Pipe::START);
        $this->mockPipe($pipe2, $this->tagsForClass(Stub\A::class), Pipe::BEFORE);
        $this->mockPipe($pipe3, $this->tagsForClass(Stub\A::class), Pipe::NORMAL);
        $this->mockPipe($pipe4, $this->tagsForClass(Stub\A::class), Pipe::AFTER);
        $this->mockPipe($pipe5, $this->tagsForClass(Stub\A::class), Pipe::END);

        $pipe1->__invoke($a1)->shouldBeCalledTimes(1)->willReturn($a2);
        $pipe2->__invoke($a2)->shouldBeCalledTimes(1)->willReturn($a3);
        $pipe3->__invoke($a3)->shouldBeCalledTimes(1)->willReturn($a4);
        $pipe4->__invoke($a4)->shouldBeCalledTimes(1)->willReturn($a5);
        $pipe5->__invoke($a5)->shouldBeCalledTimes(1)->willReturn($a6);

        $this->pump($a1)->shouldReturn($a6);
    }

    /**
     * @param Pipe[]|Collaborator[] $pipes
     */
    private function constructClassTaggerPipeline(...$pipes): void
    {
        $this->beConstructedWith(new ClassTagger(), ...$pipes);
    }

    private function tagsForClass(string ...$classes): ObjectTags
    {
        return ObjectTags::forClasses(...$classes);
    }

    /**
     * @param Pipe|Collaborator $pipe
     * @param int $order
     */
    private function mockPipe($pipe, ObjectTags $tags, int $order = Pipe::NORMAL): void
    {
        $pipe->input()->willReturn($tags);
        $pipe->order()->willReturn($order);
    }
}
