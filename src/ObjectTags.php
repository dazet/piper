<?php

namespace Piper\Pipeline;

use function array_diff_key;
use function array_keys;
use function array_map;
use function array_values;
use function count;

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

    public static function forClasses(string ...$classes): self
    {
        $tags = array_map(
            function(string $class): ObjectTag {
                return new ObjectTag($class);
            },
            $classes
        );

        return new self(...$tags);
    }

    public static function forClass(string $class, array $attributes = []): self
    {
        return new self(new ObjectTag($class, $attributes));
    }

    public static function empty(): self
    {
        static $empty;

        return $empty ?? $empty = new self();
    }

    /**
     * @return ObjectTag[]
     */
    public function values(): array
    {
        return array_values($this->items);
    }

    /**
     * @return string[]
     */
    public function valuesToString(): array
    {
        return array_keys($this->items);
    }

    public function equals(self $other): bool
    {
        return count($this->items) === count($other->items) && array_diff_key($this->items, $other->items) === [];
    }

    public function join(self $other): self
    {
        return new self(...$this->values(), ...$other->values());
    }

    public function withTag(ObjectTag ...$tags): self
    {
        return new self(...$this->values(), ...$tags);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
