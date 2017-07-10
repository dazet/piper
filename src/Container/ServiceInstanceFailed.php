<?php

namespace Piper\Container;

use Psr\Container\ContainerExceptionInterface;

final class ServiceInstanceFailed extends \RuntimeException implements ContainerExceptionInterface
{
}
