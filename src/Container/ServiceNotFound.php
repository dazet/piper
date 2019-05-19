<?php

namespace Piper\Container;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

final class ServiceNotFound extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
