<?php

namespace Piper\Http\Response;

use Piper\Pipeline\ObjectTag\PipelineLogger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Response\EmitterInterface;

final class ResponseEmitter
{
    /** @var EmitterInterface */
    private $emitter;

    /** @var ContainerInterface */
    private $container;

    public function __construct(EmitterInterface $emitter, ContainerInterface $container)
    {
        $this->emitter = $emitter;
        $this->container = $container;
    }

    public function emit(ResponseInterface $response): ResponseEmitted
    {
        $body = $response->getBody();
        $this->debugPipeline($body, $this->container->get('logger.config'));
        $this->debugPipeline($body, $this->container->get('logger.http'));

        $this->emitter->emit($response);

        return ResponseEmitted::now();
    }

    private function debugPipeline(StreamInterface $body, PipelineLogger $logger): void
    {
        $body->write("<h4>{$logger->pipeline()}</h4><ul>");
        foreach ($logger->taggedClasses() as $class) {
            $body->write("<li>{$class}</li>");
        }
        $body->write('</ul>');
    }
}
