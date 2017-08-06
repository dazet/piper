<?php

namespace Piper\Http\Routing;

final class Route
{
    public const ATTRIBUTE = 'route';

    /** @var string */
    private $name;

    /** @var string */
    private $path;

    /** @var PathPattern */
    private $pattern;

    public function __construct(string $name, string $path)
    {
        $this->name = $name;
        $this->path = $path;
        $this->pattern = new PathPattern($path);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function matchesPath(string $path): bool
    {
        return $this->pattern->matches($path);
    }

    public function extractParams(string $path): array
    {
        return $this->pattern->extractParams($path);
    }
}
