<?php

namespace Piper\Http\Routing;

use Piper\Pipe\ObjectTag;
use Piper\Pipe\ObjectTagger;
use Piper\Pipe\ObjectTags;
use Psr\Http\Message\ServerRequestInterface;

final class RoutedRequestTagger implements ObjectTagger
{
    public function tagsFor($object, ObjectTags $default): ObjectTags
    {
        if (!$object instanceof ServerRequestInterface) {
            return $default;
        }

        $route = $object->getAttribute(Route::ATTRIBUTE, null);

        if ($route instanceof Route) {
            return $default->withTag($this->routedRequestTag($route));
        }

        return $default;
    }

    private function routedRequestTag(Route $route): ObjectTag
    {
        return new ObjectTag(ServerRequestInterface::class, [Route::ATTRIBUTE => $route->name()]);
    }
}
