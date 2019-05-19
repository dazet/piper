<?php

namespace Piper\Config;

/**
 * Configuration files has following schema:
 *
 * [
 *     'parser_key_1' => [...],
 *     'parser_key_2' => [...],
 * ]
 *
 * Parser key is used to identify proper parser.
 * Parser must parse full configuration block.
 *
 * As a result it returns collection of services/values that can be registered in container.
 */
interface ConfigParser
{
    public function key(): string;

    /**
     * @return mixed
     */
    public function parse(ConfigBlock $configBlock);
}
