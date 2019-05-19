<?php

namespace Piper\Http\Routing;

use Piper\Pipeline\ObjectTag;
use Piper\Pipeline\ObjectTagger;
use Piper\Pipeline\ObjectTags;
use Psr\Http\Message\ServerRequestInterface;

final class RoutedRequestTagger implements ObjectTagger
{
    public function tagsFor(object $object, ObjectTags $default): ObjectTags
    {
        if (!$object instanceof ServerRequestInterface) {
            return $default;
        }

        $routeName = null;
        $route = $object->getAttribute(Route::ATTRIBUTE, null);

        if ($route instanceof Route) {
            $routeName = $route->name();
        }

        return $default->withTag($this->routedRequestTag($routeName));
    }

    private function routedRequestTag(?string $route): ObjectTag
    {
        return new ObjectTag(ServerRequestInterface::class, [Route::ATTRIBUTE => $route]);
    }
}
