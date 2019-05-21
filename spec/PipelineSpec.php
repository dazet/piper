<?php 

namespace spec\Piper\Pipeline;

use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Piper\Pipeline\CallablePipe;
use Piper\Pipeline\ObjectTag\ClassTagger;
use Piper\Pipeline\ObjectTags;
use Piper\Pipeline\Pipe;
use spec\Piper\Pipeline\Stub;

final class PipelineSpec extends ObjectBehavior 
{
    function it_executes_pipeline_until_there_is_not_pipe_for_the_result()
    {
        $a = new Stub\A();
        $b = new Stub\B();
        $c = new Stub\C();

        $pipe1 = CallablePipe::forClass(Stub\A::class, new Stub\ReturnValue($b));
        $pipe2 = CallablePipe::forClass(Stub\B::class, new Stub\ReturnValue($c));

        $this->constructClassTaggerPipeline($pipe1, $pipe2);

        $this->pump($a)->shouldReturn($c);
    }

    function it_does_not_pump_second_pipe_with_the_same_input_when_first_pipe_redirects_flow()
    {
        $a = new Stub\A();
        $b = new Stub\B();
        $c = new Stub\C();

        $pipe1 = CallablePipe::forClass(Stub\A::class, new Stub\ReturnValue($b));
        $pipe2 = CallablePipe::forClass(Stub\A::class, new Stub\ReturnValue($c));

        $this->constructClassTaggerPipeline($pipe1, $pipe2);

        $this->pump($a)->shouldReturn($b);
    }

    function it_continues_with_next_pipe_when_previews_has_modified_input_but_tag_has_not_changed(
        Pipe $pipe1,
        Pipe $pipe2
    ) {
        $this->constructClassTaggerPipeline($pipe1, $pipe2);

        $a1 = new Stub\A('A1');
        $a2 = new Stub\A('A2');

        $this->mockPipe($pipe1, ObjectTags::forClasses(Stub\A::class));
        $pipe1->__invoke($a1)->shouldBeCalledTimes(1)->willReturn($a2);

        $this->mockPipe($pipe2, ObjectTags::forClasses(Stub\A::class));
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

        $this->mockPipe($pipe1, ObjectTags::forClasses(Stub\A::class), Pipe::START);
        $this->mockPipe($pipe5, ObjectTags::forClasses(Stub\A::class), Pipe::END);
        $this->mockPipe($pipe2, ObjectTags::forClasses(Stub\A::class), Pipe::BEFORE);
        $this->mockPipe($pipe4, ObjectTags::forClasses(Stub\A::class), Pipe::AFTER);
        $this->mockPipe($pipe3, ObjectTags::forClasses(Stub\A::class), Pipe::NORMAL);

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
