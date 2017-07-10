<?php 

namespace spec\Piper\Pipe\ObjectTagger;

use PhpSpec\ObjectBehavior;
use Piper\Pipe\ObjectTags;
use spec\Piper\Stub;

final class ClassTaggerSpec extends ObjectBehavior 
{
    function it_creates_object_tag_from_class_name()
    {
        $object = new Stub\A();
        $tags = ObjectTags::fromClasses(Stub\B::class);

        $this->tagsFor($object, $tags)->shouldBeLike(ObjectTags::fromClasses(Stub\B::class, Stub\A::class));
    }

    function it_does_not_fail_when_input_is_not_object()
    {
        $tags = ObjectTags::fromClasses(Stub\B::class);

        $this->tagsFor('rubbish', $tags)->shouldReturn($tags);
    }
}
