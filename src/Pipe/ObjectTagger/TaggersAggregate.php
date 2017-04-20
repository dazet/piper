<?php

namespace Piper\Pipe\ObjectTagger;

use Piper\Pipe\ObjectTagger;
use Piper\Pipe\ObjectTags;

final class TaggersAggregate implements ObjectTagger
{
    /** @var ObjectTagger[] */
    private $taggers;

    public function __construct(ObjectTagger ...$taggers)
    {
        $this->taggers = $taggers;
    }

    public function tagsFor($object, ObjectTags $default): ObjectTags
    {
        $tags = $default;

        foreach ($this->taggers as $tagger) {
            $tags = $tagger->tagsFor($object, $tags);
        }

        return $tags;
    }
}
