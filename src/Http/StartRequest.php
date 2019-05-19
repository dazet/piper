<?php

namespace Piper\Http;

use function microtime;

final class StartRequest
{
    /** @var float */
    private $time;

    public function __construct(float $time = null)
    {
        $this->time = $time ?? microtime(true);
    }

    public static function now(): self
    {
        return new self(microtime(true));
    }

    public function time(): float
    {
        return $this->time;
    }
}
