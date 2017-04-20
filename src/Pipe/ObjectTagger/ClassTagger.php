<?php

namespace Piper\Pipe\ObjectTagger;

use Piper\Pipe\ObjectTag;
use Piper\Pipe\ObjectTagger;
use Piper\Pipe\ObjectTags;

final class ClassTagger implements ObjectTagger
{
    public function tagsFor($object, ObjectTags $default): ObjectTags
    {
        if (!is_object($object)) {
            return $default;
        }

        return $default->withTag(new ObjectTag(get_class($object)));
    }
}
