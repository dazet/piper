<?php

namespace Piper\Http\Routing;

final class PathFragment
{
    /** @var string */
    private $fragment;

    /** @var PathPattern */
    private $pattern;

    private function __construct(string $fragment)
    {
        $this->fragment = $fragment;
        $this->pattern = new PathPattern($fragment);
    }

    public static function create(string $fragment): self
    {
        return new self($fragment);
    }

    public static function empty(): self
    {
        static $empty;

        return $empty ?? $empty = self::create('');
    }

    /**
     * @return PathFragment[]
     */
    public static function splitPath(string $path): array
    {
        $path = trim($path, ' /');
        $path = str_replace('{/', '/{', $path);

        return array_map([self::class, 'create'], explode('/', $path));
    }

    public function matches(self $other): bool
    {
        return $this->pattern->matches($other->fragment);
    }

    public function isVariable(): bool
    {
        return $this->pattern->hasParams();
    }

    public function toString(): string
    {
        return $this->fragment;
    }
}
