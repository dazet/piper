<?php

namespace Piper\Pipe;

use Piper\Pipe;

final class CallablePipe implements Pipe
{
    /** @var callable */
    private $trigger;

    /** @var ObjectTags */
    private $input;

    /** @var int */
    private $priority;

    public function __construct(callable $trigger, ObjectTags $input, int $priority = self::NORMAL)
    {
        $this->trigger = $trigger;
        $this->input = $input;
        $this->priority = $priority;
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
        return $this->priority;
    }
}
