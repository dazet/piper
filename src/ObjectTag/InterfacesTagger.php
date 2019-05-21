<?php

namespace Piper\Pipeline\ObjectTag;

use Piper\Pipeline\ObjectTag;
use Piper\Pipeline\ObjectTagger;
use Piper\Pipeline\ObjectTags;
use function array_map;
use function array_values;
use function class_implements;
use function is_object;

final class InterfacesTagger implements ObjectTagger
{
    public function tagsFor(object $object, ObjectTags $default): ObjectTags
    {
        if (!is_object($object)) {
            return $default;
        }

        $interfaces = class_implements($object) ?: [];

        if (empty($interfaces)) {
            return $default;
        }

        $tags = array_map([ObjectTag::class, 'fromClass'], $interfaces);

        return $default->withTag(...array_values($tags));
    }
}
