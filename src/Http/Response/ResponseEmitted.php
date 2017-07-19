<?php

namespace Piper\Http\Response;

final class ResponseEmitted
{
    /** @var float */
    private $time;

    public function __construct(float $time)
    {
        $this->time = $time;
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
