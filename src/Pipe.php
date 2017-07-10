<?php

namespace Piper;

use Piper\Pipe\ObjectTags;

interface Pipe
{
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
