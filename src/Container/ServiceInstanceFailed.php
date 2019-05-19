<?php

namespace Piper\Container;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class ServiceInstanceFailed extends RuntimeException implements ContainerExceptionInterface
{
}
