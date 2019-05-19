<?php 

namespace spec\Piper\Pipeline\ObjectTag;

use PhpSpec\ObjectBehavior;
use Piper\Pipeline\ObjectTagger;
use Piper\Pipeline\ObjectTags;
use spec\Piper\Stub;

final class TaggersAggregateSpec extends ObjectBehavior 
{
    function it_aggregates_multiple_taggers(ObjectTagger $tagger1, ObjectTagger $tagger2)
    {
        $this->beConstructedWith($tagger1, $tagger2);

        $object = new Stub\A();
        $tags0 = new ObjectTags();
        $tags1 = ObjectTags::forClasses(Stub\A::class);
        $tags2 = ObjectTags::forClasses(Stub\A::class, Stub\B::class);

        $tagger1->tagsFor($object, $tags0)->shouldBeCalledTimes(1)->willReturn($tags1);
        $tagger2->tagsFor($object, $tags1)->shouldBeCalledTimes(1)->willReturn($tags2);

        $this->tagsFor($object, $tags0)->shouldReturn($tags2);
    }
}
