<?php

namespace Piper\Config;

use Piper\Container\Container;
use Piper\Container\Services;

final class RegisterServices
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(Services $services): void
    {
        $this->container->addServices($services);
    }
}
