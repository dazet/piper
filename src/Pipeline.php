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

        foreach ($pipes as $pipe) {
            foreach ($pipe->input()->items() as $tag) {
                $this->pipes[$tag->toString()][] = $pipe;
            }
        }
    }

    /**
     * @param object $input Pipeline input object.
     * @param callable|null $restHandler Callback for last unhandled pipe output.
     */
    public function pump($input, callable $restHandler = null): void
    {
        $inputTags = $this->tagsFor($input);
        $current = $input;
        $currentTags = $inputTags;

        foreach ($this->pipesFor($inputTags) as $pipe) {
            $trigger = $pipe->trigger();
            $output = $trigger($current);
            $outputTags = $this->tagsFor($output);

            // Pipe is clogged (dead end), try next pipe with current input.
            if ($outputTags->isEmpty()) {
                continue;
            }

            // Pipe has transformed input, start with new set of pipes.
            if (!$outputTags->equals($currentTags)) {
                $this->pump($output, $restHandler);

                return;
            }

            // Pipe has returned original or slightly modified input, continue with next pipe.
            $current = $output;
            $currentTags = $outputTags;
        }

        if ($restHandler !== null) {
            $restHandler($current);
        }
    }

    /**
     * @return Pipe[]
     */
    private function pipesFor(ObjectTags $tags): array
    {
        $pipes = [];

        foreach ($tags->items() as $tag) {
            $pipes[] = $this->pipes[$tag->toString()] ?? [];
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
}
