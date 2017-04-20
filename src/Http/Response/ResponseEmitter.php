<?php

namespace Piper\Http\Response;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;

final class ResponseEmitter
{
    /** @var EmitterInterface */
    private $emitter;

    public function __construct(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    public function emit(ResponseInterface $response): ResponseEmitted
    {
        $this->emitter->emit($response);

        return ResponseEmitted::now();
    }
}
