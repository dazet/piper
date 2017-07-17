<?php

namespace Piper\Pipe;

interface ObjectTagger
{
    /**
     * @param object $object
     */
    public function tagsFor($object, ObjectTags $default): ObjectTags;
}
