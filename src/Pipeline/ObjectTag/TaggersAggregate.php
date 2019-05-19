<?php

namespace Piper\Pipeline\ObjectTag;

use Piper\Pipeline\ObjectTagger;
use Piper\Pipeline\ObjectTags;
use function array_reduce;

final class TaggersAggregate implements ObjectTagger
{
    /** @var ObjectTagger[] */
    private $taggers;

    public function __construct(ObjectTagger ...$taggers)
    {
        $this->taggers = $taggers;
    }

    public static function default(ObjectTagger ...$taggers): self
    {
        return new self(new ClassTagger(), new InterfacesTagger(), ...$taggers);
    }

    public function tagsFor(object $object, ObjectTags $default): ObjectTags
    {
        return array_reduce(
            $this->taggers,
            function (ObjectTags $current, ObjectTagger $tagger) use ($object): ObjectTags {
                return $tagger->tagsFor($object, $current);
            },
            $default
        );
    }

    /**
     * @return ObjectTagger[]
     */
    public function taggers(): array
    {
        return $this->taggers;
    }
}
