<?php

namespace Piper;

use Piper\Pipe\ObjectTags;

interface Pipe
{
    public const START = -100;
    public const BEFORE = -10;
    public const NORMAL = 0;
    public const AFTER = 10;
    public const END = 100;

    /**
     * Pipe trigger callback accepting 1 argument declared by `input` method.
     * @return callable
     */
    public function trigger(): callable;

    /**
     * Trigger input types declaration.
     * @return ObjectTags
     */
    public function input(): ObjectTags;

    /**
     * Pipe order in a set of pipes with the same input.
     * @return int [-100..100]
     */
    public function order(): int;
}
