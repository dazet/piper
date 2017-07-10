<?php

namespace Piper\Container;

use Psr\Container\NotFoundExceptionInterface;

final class ServiceNotFound extends \InvalidArgumentException implements NotFoundExceptionInterface
{
}
