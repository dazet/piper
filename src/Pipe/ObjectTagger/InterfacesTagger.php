<?php

namespace Piper\Pipe\ObjectTagger;

use Piper\Pipe\ObjectTag;
use Piper\Pipe\ObjectTagger;
use Piper\Pipe\ObjectTags;

final class InterfacesTagger implements ObjectTagger
{
    public function tagsFor($object, ObjectTags $default): ObjectTags
    {
        if (!is_object($object)) {
            return $default;
        }

        $tags = array_map(
            function (string $interface): ObjectTag {
                return new ObjectTag($interface);
            },
            class_implements($object) ?: []
        );

        return $default->withTag(...array_values($tags));
    }
}
