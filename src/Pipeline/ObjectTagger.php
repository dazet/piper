<?php

namespace Piper\Pipeline;

interface ObjectTagger
{
    public function tagsFor(object $object, ObjectTags $default): ObjectTags;
}
