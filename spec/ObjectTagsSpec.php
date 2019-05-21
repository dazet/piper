<?php 

namespace spec\Piper\Pipeline;

use PhpSpec\ObjectBehavior;
use Piper\Pipeline\ObjectTag;
use Piper\Pipeline\ObjectTags;
use spec\Piper\Pipeline\Stub;

final class ObjectTagsSpec extends ObjectBehavior 
{
    function it_is_collection_of_object_tag_instances()
    {
        $tag1 = new ObjectTag(Stub\A::class);
        $tag2 = new ObjectTag(Stub\B::class);

        $this->beConstructedWith($tag1, $tag2);

        $this->values()->shouldReturn([$tag1, $tag2]);
    }

    function it_can_be_equal_than_other_tags()
    {
        $tag1 = new ObjectTag('Tag1');
        $tag2 = new ObjectTag('Tag2');

        $this->beConstructedWith($tag1, $tag2);

        $this->equals(new ObjectTags($tag2, $tag1))->shouldBe(true);
        $this->equals(new ObjectTags($tag1, $tag2))->shouldBe(true);
    }

    function it_can_be_not_equal_than_other_tags()
    {
        $tag1 = new ObjectTag('Tag1');
        $tag2 = new ObjectTag('Tag2');

        $this->beConstructedWith($tag1, $tag2);

        $this->equals(new ObjectTags($tag2))->shouldBe(false);
        $this->equals(new ObjectTags($tag1))->shouldBe(false);
        $this->equals(new ObjectTags($tag1, $tag2, new ObjectTag('Tag3')))->shouldBe(false);
    }

    function it_can_be_joined_with_other_tags()
    {
        $tag1 = new ObjectTag('Tag1');
        $tag2 = new ObjectTag('Tag2');

        $this->beConstructedWith($tag1, $tag2);

        $tag3 = new ObjectTag('Tag3');
        $tag4 = new ObjectTag('Tag4');

        $joined = $this->join(new ObjectTags($tag3, $tag4));
        $joined->shouldNotBe($this);
        $joined->shouldBeLike(new ObjectTags($tag1, $tag2, $tag3, $tag4));
    }

    function it_can_append_tags()
    {
        $tag1 = new ObjectTag('Tag1');
        $tag2 = new ObjectTag('Tag2');

        $this->beConstructedWith($tag1, $tag2);

        $tag3 = new ObjectTag('Tag3');
        $tag4 = new ObjectTag('Tag4');

        $withTags = $this->withTag($tag3, $tag4);
        $withTags->shouldNotBe($this);
        $withTags->shouldBeLike(new ObjectTags($tag1, $tag2, $tag3, $tag4));
    }

    function it_can_be_empty()
    {
        $this->beConstructedWith();

        $this->isEmpty()->shouldBe(true);
    }

    function it_can_tell_if_is_not_empty()
    {
        $this->beConstructedWith(new ObjectTag('Tag'));

        $this->isEmpty()->shouldBe(false);
    }
}
