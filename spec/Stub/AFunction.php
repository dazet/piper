<?php declare(strict_types=1);

namespace spec\Piper\Stub;

final class AFunction
{
    /** @var mixed */
    private $next;

    public function __construct($next)
    {
        $this->next = $next;
    }

    public function __invoke(A $a)
    {
        return $this->next;
    }
}
