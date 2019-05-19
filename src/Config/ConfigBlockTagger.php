<?php

namespace Piper\Config;

use Piper\Pipeline\ObjectTag;
use Piper\Pipeline\ObjectTagger;
use Piper\Pipeline\ObjectTags;

/**
 * ConfigBlockTagger tags ConfigBlock with parser key.
 */
final class ConfigBlockTagger implements ObjectTagger
{
    public function tagsFor(object $block, ObjectTags $default): ObjectTags
    {
        if (!$block instanceof ConfigBlock) {
            return $default;
        }

        return $default->withTag(new ObjectTag(ConfigBlock::class, ['key' => $block->key()]));
    }
}
