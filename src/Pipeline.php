<?php

namespace Piper;

use Piper\Pipe\ObjectTagger;
use Piper\Pipe\ObjectTags;

final class Pipeline
{
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
     * @param callable|null $finished Callback for last unhandled pipe output.
     */
    public function pump($input, callable $finished = null): void
    {
        $inputTags = $this->tagger->tagsFor($input, new ObjectTags());
        $current = $input;
        $currentTags = $inputTags;

        foreach ($this->pipesFor($inputTags) as $pipe) {
            $trigger = $pipe->trigger();
            $output = $trigger($current);
            $outputTags = $this->tagger->tagsFor($output, new ObjectTags());

            // 3 possible scenarios:
            // - pipe is clogged (dead end), try next pipe with same input...
            if ($outputTags->isEmpty()) {
                continue;
            }

            // - pipe has transformed input, start with new set of pipes...
            if (!$outputTags->equals($currentTags)) {
                $this->pump($output, $finished);

                return;
            }

            // - pipe has just filtered input, continue with next pipe.
            $current = $output;
            $currentTags = $outputTags;
        }

        if ($finished !== null) {
            $finished($current);
        }
    }

    /**
     * @return Pipe[]
     */
    private function pipesFor(ObjectTags $tags): array
    {
        $pipes = array_filter($this->pipes, function(Pipe $pipe) use ($tags): bool {
            return $pipe->input()->matches($tags);
        });

        uasort($pipes, function(Pipe $a, Pipe $b) {
            return $a->order() <=> $b->order();
        });

        return $pipes;
    }
}
