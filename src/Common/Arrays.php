<?php

namespace Piper\Common;

class Arrays
{
    public static function mapWithKey(callable $callback, array $assocArray): array
    {
        return array_map($callback, $assocArray, array_keys($assocArray));
    }

    public static function excludeKeys(array $array, string ...$keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    private function __construct()
    {
    }
}
