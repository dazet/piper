<?php

namespace Piper\Http\Routing;

use Psr\Http\Message\ServerRequestInterface;

final class Route
{
    public const ATTRIBUTE = 'route';

    /** @var string */
    private $name;

    /** @var string */
    private $path;

    /** @var string */
    private $pattern;

    /** @var string[] */
    private $patternKeys = [];

    public function __construct(string $name, string $path)
    {
        $this->name = $name;
        $this->path = $path;
        $this->pattern = $this->buildPattern();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function applyTo(ServerRequestInterface $request): ServerRequestInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if (!$this->matchesPath($path)) {
            return $request;
        }

        foreach ($this->pathParams($path) as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $request = $request->withAttribute(self::ATTRIBUTE, $this);

        return $request;
    }

    private function buildPattern(): string
    {
        // quote all by default
        $pattern = preg_quote($this->path(), '~');

        // replace quoted {paramName} with named sub-pattern
        $pattern = preg_replace_callback(
            '~\\{([a-zA-Z]+)\\}~',
            function(array $m): string {
                $this->patternKeys[$m[1]] = $m[1];

                return '(?P<' . $m[1] . '>[^/]+)';
            },
            $pattern
        );

        return "~^{$pattern}$~i";
    }

    private function matchesPath(string $path): bool
    {
        return preg_match($this->pattern, $path) === 1;
    }

    private function pathParams(string $path): array
    {
        preg_match($this->pattern, $path, $m);

        return array_intersect_key($m, $this->patternKeys);
    }
}
