<?php

namespace Piper\Pipe;

interface ObjectTagger
{
    /**
     * @param object $object
     * @param ObjectTags $default
     * @return ObjectTags
     */
    public function tagsFor($object, ObjectTags $default): ObjectTags;
}
