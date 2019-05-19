<?php

namespace Piper\Pipeline;

use Generator;
use InvalidArgumentException;
use Piper\Pipeline\ObjectTag\TaggersAggregate;
use Piper\Pipeline\Util\CallableReflector;
use function array_map;
use function array_merge;

final class PipelineBuilder
{
    /** @var TaggersAggregate */
    private $tagger;

    /** @var Pipe[] */
    private $pipes;

    /** @var CallableReflector */
    private $callableReflector;

    private function __construct()
    {
        $this->tagger = TaggersAggregate::default();
        $this->pipes = [];
        $this->callableReflector = new CallableReflector();
    }

    public static function new(): self
    {
        return new self();
    }

    public function tagBy(ObjectTagger ...$taggers): self
    {
        $copy = clone $this;
        $copy->tagger = new TaggersAggregate(...$this->tagger->taggers(), ...$taggers);

        return $copy;
    }

    public function pipe(callable ...$pipes): self
    {
        $copy = clone $this;
        $copy->pipes = array_merge($this->pipes, array_map([$this, 'callableToPipe'], $pipes));

        return $copy;
    }

    public function pipeFor(string $inputClass, callable $callback): self
    {
        return $this->pipe(new CallablePipe($callback, ObjectTags::forClass($inputClass)));
    }

    public function forking(): self
    {
        $pipeline = $this->build();

        return $this->pipeFor(Generator::class, new ForkPipeline($pipeline));
    }

    public function build(): Pipeline
    {
        return new Pipeline($this->tagger, ...$this->pipes);
    }

    private function callableToPipe(callable $callback): Pipe
    {
        if ($callback instanceof Pipe) {
            return $callback;
        }

        $inputClass = $this->callableReflector->getParameterClass($callback);

        if ($inputClass === null) {
            throw new InvalidArgumentException('Unable to resolve pipe input parameter');
        }

        return new CallablePipe($callback, ObjectTags::forClass($inputClass));
    }
}
