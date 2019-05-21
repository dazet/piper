<?php

namespace Piper\Pipeline\Util;

use function array_map;

final class StringUtil
{
    public static function prepend(string $prefix, string $string): string
    {
        return $prefix . $string;
    }

    public static function prependAll(string $prefix, string ...$strings): array
    {
        return array_map(
            function(string $string) use ($prefix): string {
                return self::prepend($prefix, $string);
            },
            $strings
        );
    }
}
