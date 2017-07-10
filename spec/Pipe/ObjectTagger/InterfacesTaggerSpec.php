<?php 

namespace spec\Piper\Pipe\ObjectTagger;

use PhpSpec\ObjectBehavior;
use Piper\Pipe\ObjectTags;
use spec\Piper\Stub;

final class InterfacesTaggerSpec extends ObjectBehavior 
{
    function it_creates_object_tags_from_object_interfaces()
    {
        $object = new Stub\A();
        $tags = new ObjectTags();

        $this->tagsFor($object, $tags)
            ->shouldBeLike(ObjectTags::fromClasses(Stub\AInterface::class, Stub\XInterface::class));
    }

    function it_does_nothing_when_class_has_no_interface()
    {
        $object = new Stub\C();
        $tags = new ObjectTags();

        $this->tagsFor($object, $tags)->shouldReturn($tags);
    }

    function it_does_nothing_when_input_is_not_object()
    {
        $tags = new ObjectTags();

        $this->tagsFor('rubbish', $tags)->shouldReturn($tags);
    }
}
