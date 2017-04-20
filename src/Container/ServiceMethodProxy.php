<?php

namespace Piper\Container;

use Psr\Container\ContainerInterface;

final class ServiceMethodProxy
{
    /** @var \Closure */
    private $callback;

    public function __construct(ContainerInterface $container, string $serviceId, string $method = null)
    {
        // Lazy initialize service on firs call and don`t store dependencies that are no longer needed.
        $this->callback = function(...$args) use ($container, $serviceId, $method) {
            $service = $container->get($serviceId);
            $callable = $method === null ? $service : [$service, $method];
            $this->callback = \Closure::fromCallable($callable);

            return ($this->callback)(...$args);
        };
    }

    public function __invoke(...$args)
    {
        return ($this->callback)(...$args);
    }
}
