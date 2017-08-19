<?php

namespace Piper;

use Piper\Pipe\ObjectTagger;
use Piper\Pipe\ObjectTags;

final class Pipeline
{
    public const START = -100;
    public const BEFORE = -10;
    public const NORMAL = 0;
    public const AFTER = 10;
    public const END = 100;

    /** @var Pipe[][] */
    private $pipes = [];

    /** @var ObjectTagger */
    private $tagger;

    public function __construct(ObjectTagger $tagger, Pipe ...$pipes)
    {
        $this->tagger = $tagger;
        $this->addPipes(...$pipes);
    }

    /**
     * @param object $input Pipeline input object.
     * @param callable|null $restHandler Callback for last not empty pipe output.
     */
    public function pump($input, callable $restHandler = null): void
    {
        if ($input instanceof \Generator) {
            $this->forkPipeline($input, $restHandler);

            return;
        }

        $inputTags = $this->tagsFor($input);

        $current = $input;
        $currentTags = $inputTags;

        foreach ($this->pipesFor($inputTags) as $pipe) {
            $output = ($pipe->trigger())($current);
            $outputTags = $this->tagsFor($output);
            $notEmptyOutput = !$outputTags->isEmpty();

            if ($notEmptyOutput && !$outputTags->equals($currentTags)) {
                $this->pump($output, $restHandler);

                return;
            }

            if ($notEmptyOutput) {
                $current = $output;
                $currentTags = $outputTags;
            }
        }

        if ($restHandler !== null) {
            $restHandler($current);
        }
    }

    public function withPipes(Pipe ...$pipes): self
    {
        $copy = clone $this;
        $copy->addPipes(...$pipes);

        return $copy;
    }

    /**
     * @return Pipe[]
     */
    private function pipesFor(ObjectTags $inputTags): array
    {
        $pipes = [];

        foreach ($inputTags->values() as $tag) {
            $pipes[] = $this->pipes[$tag->toString()] ?? [];
        }

        if ($pipes === []) {
            return [];
        }

        $pipes = array_merge(...$pipes);
        uasort($pipes, function(Pipe $a, Pipe $b): int {
            return $a->order() <=> $b->order();
        });

        return $pipes;
    }

    /**
     * @param object|null $input
     */
    private function tagsFor($input): ObjectTags
    {
        return $this->tagger->tagsFor($input, ObjectTags::empty());
    }

    private function addPipes(Pipe ...$pipes): void
    {
        foreach ($pipes as $pipe) {
            foreach ($pipe->input()->values() as $tag) {
                $this->pipes[$tag->toString()][] = $pipe;
            }
        }
    }

    private function forkPipeline(\Generator $source, ?callable $restHandler): void
    {
        foreach ($source as $input) {
            $this->pump($input, $restHandler);
        }
    }
}
