<?php

namespace Piper\Pipe;

use Piper\Pipe;
use Piper\Pipeline;

final class CallablePipe implements Pipe
{
    /** @var callable */
    private $trigger;

    /** @var ObjectTags */
    private $input;

    /** @var int */
    private $order;

    public function __construct(callable $trigger, ObjectTags $input, int $order = Pipeline::NORMAL)
    {
        $this->trigger = $trigger;
        $this->input = $input;
        $this->order = $order;
    }

    public function trigger(): callable
    {
        return $this->trigger;
    }

    public function input(): ObjectTags
    {
        return $this->input;
    }

    public function order(): int
    {
        return $this->order;
    }
}
