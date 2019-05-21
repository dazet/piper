<?php

namespace Piper\Pipeline\Util;

use Closure;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use function is_array;
use function is_object;
use function is_string;

final class CallableReflector
{
    /**
     * Get callable parameters class or null if not defined or not a class.
     */
    public function getParameterClass(callable $callable, int $offset = 0): ?string
    {
        $reflection = $this->resolveReflection($callable);
        $parameter = $reflection->getParameters()[$offset] ?? null;

        if ($parameter === null) {
            return null;
        }

        $type = $parameter->getType();
        if ($type === null || $type->isBuiltin()) {
            return null;
        }

        return $type->getName();
    }

    private function resolveReflection(callable $callable): ReflectionFunctionAbstract
    {
        if (is_array($callable) && is_object($callable[0])) {
            return new ReflectionMethod($callable[0], $callable[1]);
        }

        if ($callable instanceof Closure || is_string($callable)) {
            return new ReflectionFunction($callable);
        }

        if (is_object($callable)) {
            return new ReflectionMethod($callable, '__invoke');
        }

        return new ReflectionFunction(Closure::fromCallable($callable));
    }
}
