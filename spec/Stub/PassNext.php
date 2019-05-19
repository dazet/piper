<?php declare(strict_types=1);

namespace spec\Piper\Stub;

final class PassNext
{
    /** @var mixed */
    private $next;

    public function __construct($next)
    {
        $this->next = $next;
    }

    /**
     * @param mixed $arg
     * @return mixed mixed
     */
    public function __invoke($arg)
    {
        return $this->next;
    }
}
