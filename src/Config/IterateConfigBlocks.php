<?php

namespace Piper\Config;

use Generator;

final class IterateConfigBlocks
{
    public function __invoke(ConfigFile $config): Generator
    {
        yield from $config->blocks();
    }
}
