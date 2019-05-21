<?php 

namespace spec\Piper\Pipeline\ObjectTag;

use PhpSpec\ObjectBehavior;
use Piper\Pipeline\ObjectTags;
use spec\Piper\Pipeline\Stub;

final class ClassTaggerSpec extends ObjectBehavior 
{
    function it_creates_object_tag_from_class_name()
    {
        $object = new Stub\A();
        $tags = ObjectTags::forClasses(Stub\B::class);

        $this->tagsFor($object, $tags)->shouldBeLike(ObjectTags::forClasses(Stub\B::class, Stub\A::class));
    }
}
