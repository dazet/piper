<?php declare(strict_types=1);

namespace spec\Piper\Stub;

final class BFunction
{
    /** @var mixed */
    private $next;

    public function __construct($next)
    {
        $this->next = $next;
    }

    public function __invoke(B $b)
    {
        return $this->next;
    }
}
