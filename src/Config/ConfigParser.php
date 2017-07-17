<?php

namespace Piper\Config;

use Piper\Container\Services;

/**
 * Configuration files has following schema:
 *
 * [
 *     'parser_key_1' => [...],
 *     'parser_key_2' => [...],
 * ]
 *
 * Parser key is used to identify valid parser service.
 * Parser must parse full configuration block.
 */
interface ConfigParser
{
    public function key(): string;

    public function parse(array $configBlock): Services;
}
