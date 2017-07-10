<?php 

namespace spec\Piper\Pipe;

use PhpSpec\ObjectBehavior;
use Piper\Pipe\ObjectTag;
use spec\Piper\Stub;

final class ObjectTagSpec extends ObjectBehavior 
{
    function it_tags_object_by_class_name()
    {
        $this->beConstructedWith(ObjectTag::class);

        $this->class()->shouldReturn(ObjectTag::class);
        $this->attributes()->shouldReturn([]);
    }

    function it_tags_object_by_class_name_and_public_attributes()
    {
        $this->beConstructedWith(ObjectTag::class, ['class' => ObjectTag::class]);

        $this->class()->shouldReturn(ObjectTag::class);
        $this->attributes()->shouldReturn(['class' => ObjectTag::class]);
    }

    function it_does_not_care_if_class_exists()
    {
        $class = 'This\Class\Does\Not\Exist';
        $this->beConstructedWith($class);

        $this->class()->shouldReturn($class);
    }

    function it_can_be_exported_to_json()
    {
        $this->beConstructedWith(Stub\A::class, ['value' => 'AAA']);

        $json = json_encode(['class' => Stub\A::class, 'attributes' => ['value' => 'AAA']]);

        $this->toString()->shouldReturn($json);
    }

    function it_can_be_created_from_json()
    {
        $tag = new ObjectTag(Stub\A::class, ['value' => 'ABC']);

        $this->beConstructedThrough('fromJson', [$tag->toString()]);

        $this->class()->shouldBe(Stub\A::class);
        $this->attributes()->shouldBe(['value' => 'ABC']);
    }
}
