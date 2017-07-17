<?php

namespace Piper;

use Piper\Pipe\ObjectTags;

interface Pipe
{
    /** Pipe trigger callback accepting 1 argument declared by `input` method. */
    public function trigger(): callable;

    /** Trigger input types declaration. */
    public function input(): ObjectTags;

    /** Pipe order in a set of pipes with the same input. */
    public function order(): int;
}
