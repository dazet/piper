<?php declare(strict_types=1);

namespace Piper\Pipeline\ObjectTag;

use Piper\Pipeline\ObjectTagger;
use Piper\Pipeline\ObjectTags;
use function get_class;

final class PipelineLogger implements ObjectTagger
{
    /** @var string */
    private $pipeline;

    /** @var string[] */
    private $taggedClasses = [];

    public function __construct(string $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    public function tagsFor(object $object, ObjectTags $default): ObjectTags
    {
        $this->taggedClasses[] = get_class($object);

        return $default;
    }

    public function pipeline(): string
    {
        return $this->pipeline;
    }

    /**
     * @return string[]
     */
    public function taggedClasses(): array
    {
        return $this->taggedClasses;
    }
}
