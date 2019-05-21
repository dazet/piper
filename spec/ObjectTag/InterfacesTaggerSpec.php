<?php 

namespace spec\Piper\Pipeline\ObjectTag;

use PhpSpec\ObjectBehavior;
use Piper\Pipeline\ObjectTags;
use spec\Piper\Pipeline\Stub;

final class InterfacesTaggerSpec extends ObjectBehavior 
{
    function it_creates_object_tags_from_object_interfaces()
    {
        $object = new Stub\A();
        $tags = new ObjectTags();

        $this->tagsFor($object, $tags)
            ->shouldBeLike(ObjectTags::forClasses(Stub\AInterface::class, Stub\XInterface::class));
    }

    function it_does_nothing_when_class_has_no_interface()
    {
        $object = new Stub\C();
        $tags = new ObjectTags();

        $this->tagsFor($object, $tags)->shouldReturn($tags);
    }
}
