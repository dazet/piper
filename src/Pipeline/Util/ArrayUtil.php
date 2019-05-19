<?php

namespace Piper\Pipeline\Util;

use InvalidArgumentException;
use function array_diff_key;
use function array_flip;
use function array_intersect_key;
use function array_keys;
use function array_map;
use function json_encode;

class ArrayUtil
{
    public static function mapAssoc(callable $callback, array $assocArray): array
    {
        return array_map($callback, $assocArray, array_keys($assocArray));
    }

    public static function except(array $array, string ...$keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    public static function only(array $array, string ...$keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    public static function jsonEncode(array $array): string
    {
        $json = json_encode($array);

        if ($json === false) {
            throw new InvalidArgumentException('Value cannot be encoded to json');
        }

        return $json;
    }

    private function __construct()
    {
    }
}
