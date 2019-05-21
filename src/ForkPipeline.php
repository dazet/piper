<?php declare(strict_types=1);

namespace Piper\Pipeline;

final class ForkPipeline
{
    /** @var Pipeline */
    private $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    public function __invoke(iterable $stream): void
    {
        foreach ($stream as $input) {
            $this->pipeline->pump($input);
        }
    }
}
