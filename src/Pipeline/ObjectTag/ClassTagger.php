<?php

namespace Piper\Pipeline\ObjectTag;

use Piper\Pipeline\ObjectTag;
use Piper\Pipeline\ObjectTagger;
use Piper\Pipeline\ObjectTags;

final class ClassTagger implements ObjectTagger
{
    public function tagsFor(object $object, ObjectTags $default): ObjectTags
    {
        return $default->withTag(ObjectTag::fromClass(get_class($object)));
    }
}
