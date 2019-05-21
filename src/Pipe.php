<?php

namespace Piper\Pipeline;

interface Pipe
{
    public const START = -100;
    public const BEFORE = -10;
    public const NORMAL = 0;
    public const AFTER = 10;
    public const END = 100;

    public function __invoke(object $object): ?object;

    /**
     * Pipe input matching types declaration.
     */
    public function input(): ObjectTags;

    /**
     * Pipe order in a set of pipes with the same input.
     * Lower value means that pipe should be called earlier, higher means later.
     */
    public function order(): int;
}
