<?php declare(strict_types=1);

namespace Piper\Pipeline;

use Piper\Pipeline\Util\ArrayUtil;
use function array_merge;
use function array_values;
use function uasort;

final class Pipes
{
    /** @var Pipe[][] */
    private $pipes;

    public function __construct(Pipe ...$pipes)
    {
        $this->addPipes(...$pipes);
    }

    /**
     * @return Pipe[]
     */
    public function matching(ObjectTags $inputTags): array
    {
        $pipes = ArrayUtil::only($this->pipes, ...$inputTags->valuesToString());

        $pipes = array_merge([], ...array_values($pipes));
        uasort($pipes, function(Pipe $a, Pipe $b): int {
            return $a->order() <=> $b->order();
        });

        return $pipes;
    }

    public function with(Pipe ...$pipes): self
    {
        $clone = clone $this;
        $clone->addPipes(...$pipes);

        return $clone;
    }

    private function addPipes(Pipe ...$pipes): void
    {
        foreach ($pipes as $pipe) {
            foreach ($pipe->input()->values() as $tag) {
                $this->pipes[$tag->toString()][] = $pipe;
            }
        }
    }
}
