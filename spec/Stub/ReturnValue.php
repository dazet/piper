<?php declare(strict_types=1);

namespace spec\Piper\Pipeline\Stub;

final class ReturnValue
{
    /** @var mixed */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param mixed $arg
     * @return mixed mixed
     */
    public function __invoke($arg)
    {
        return $this->value;
    }
}
