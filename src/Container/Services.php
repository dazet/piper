<?php

namespace Piper\Container;

final class Services
{
    /** @var Service[] */
    private $items;

    public function __construct(Service ...$items)
    {
        $this->items = $items;
    }

    /**
     * @return Service[]
     */
    public function all(): array
    {
        return $this->items;
    }

    public function join(self $other): self
    {
        return new self(...$this->items, ...$other->items);
    }
}
