<?php

namespace Piper\Pipeline;

final class Pipeline
{
    /** @var Pipes */
    private $pipes;

    /** @var ObjectTagger */
    private $tagger;

    public function __construct(ObjectTagger $tagger, Pipe ...$pipes)
    {
        $this->tagger = $tagger;
        $this->pipes = new Pipes(...$pipes);
    }

    public function pump(object $input): object
    {
        return $this->pumpPipeline($input, $this->tagsFor($input));
    }

    private function pumpPipeline(object $input, ObjectTags $inputTags): object
    {
        $current = $input;
        $currentTags = $inputTags;

        foreach ($this->pipes->matching($inputTags) as $pipe) {
            $next = $pipe($current);

            if ($next === null) {
                continue;
            }

            $nextTags = $this->tagsFor($next);

            if (!$nextTags->equals($currentTags)) {
                return $this->pumpPipeline($next, $nextTags);
            }

            $current = $next;
            $currentTags = $nextTags;
        }

        return $current;
    }

    public function withPipes(Pipe ...$pipes): self
    {
        $copy = clone $this;
        $copy->pipes = $this->pipes->with(...$pipes);

        return $copy;
    }

    private function tagsFor(?object $input): ObjectTags
    {
        if ($input === null) {
            return ObjectTags::empty();
        }

        return $this->tagger->tagsFor($input, ObjectTags::empty());
    }
}
