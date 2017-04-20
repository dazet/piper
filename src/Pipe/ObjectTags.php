<?php

namespace Piper\Pipe;

final class ObjectTags
{
    /** @var ObjectTag[] */
    private $items = [];

    public function __construct(ObjectTag ...$tags)
    {
        foreach ($tags as $tag) {
            $this->items[$tag->toString()] = $tag;
        }
    }

    public static function fromClasses(string ...$classes): self
    {
        $tags = array_map(
            function (string $class): ObjectTag {
                return new ObjectTag($class);
            },
            $classes
        );

        return new self(...$tags);
    }

    /**
     * @return ObjectTag[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function matches(ObjectTags $other): bool
    {
        return !empty(array_intersect_key($this->items, $other->items));
    }

    public function equals(self $other): bool
    {
        return empty(array_diff_key($this->items, $other->items));
    }

    public function join(self $other): self
    {
        return new self(...$this->items, ...$other->items);
    }

    public function withTag(ObjectTag ...$tags): self
    {
        return new self(...array_values($this->items), ...$tags);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
