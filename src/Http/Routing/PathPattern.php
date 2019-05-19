<?php

namespace Piper\Http\Routing;

use LogicException;
use function is_string;
use function preg_match;
use function preg_quote;
use function preg_replace_callback;

final class PathPattern
{
    /** @var string */
    private $path;

    /** @var string */
    private $pattern;

    /** @var string[] */
    private $params = [];

    /** @var string[] */
    private $paramsPatterns;

    public function __construct(string $path, array $paramsPatterns = [])
    {
        $this->path = $path;
        $this->paramsPatterns = $paramsPatterns;
    }

    public function matches(string $path): bool
    {
        $this->initPattern();

        return preg_match($this->pattern, $path) === 1;
    }

    public function hasParams(): bool
    {
        $this->initPattern();

        return $this->params !== [];
    }

    public function extractParams(string $path): array
    {
        $this->initPattern();
        preg_match($this->pattern, $path, $m);

        $params = array_intersect_key($m, $this->params);

        return array_map(
            function(string $value): ?string {
                return $value !== '' ? $value : null;
            },
            $params
        );
    }

    private function initPattern(): void
    {
        if ($this->pattern !== null) {
            return;
        }

        // quote all by default
        $pathPattern = preg_quote($this->path, '~');

        // replace {paramName} with named sub-pattern
        $pathPattern = preg_replace_callback(
            '~\\\{([\w\_]+)\\\}~',
            function(array $m): string {
                $param = $m[1];
                // store captured param key
                $this->params[$param] = $param;
                $paramPattern = $this->paramsPatterns[$param] ?? '[^/]';

                return '(?P<' . $param . '>' . $paramPattern . '+)';
            },
            $pathPattern
        );

        if (!is_string($pathPattern)) {
            throw new LogicException('Broken pathPattern');
        }

        // replace optional {paramName?} or {/paramName?} with named sub-pattern
        $pathPattern = preg_replace_callback(
            '~\\\{(/?)([\w\_]+)\\\\\?\\\}~',
            function(array $m): string {
                [, $slash, $param] = $m;
                // store captured param key
                $this->params[$param] = $param;
                $paramPattern = $this->paramsPatterns[$param] ?? '[^/]';

                return ($slash === '/' ? '/?' : '') . '(?P<' . $param . '>' . $paramPattern . '*)';
            },
            $pathPattern
        );

        $this->pattern = "~^{$pathPattern}$~i";
    }
}
