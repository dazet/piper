<?php

namespace spec\Piper\Pipeline\Stub;

class A implements AInterface, XInterface
{
    public $value;

    public function __construct($value = 'A')
    {
        $this->value = $value;
    }
}
