<?php

namespace Piper\Pipeline;

final class CallablePipe implements Pipe
{
    /** @var callable */
    private $callback;

    /** @var ObjectTags */
    private $input;

    /** @var int */
    private $order;

    public function __construct(callable $callback, ObjectTags $input, int $order = Pipe::NORMAL)
    {
        $this->callback = $callback;
        $this->input = $input;
        $this->order = $order;
    }

    public function __invoke(object $object): ?object
    {
        return ($this->callback)($object);
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
