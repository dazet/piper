<?php

use League\Container\Container;
use League\Container\ReflectionContainer;

return [
    [
        'input'            => ['class' => Container::class],
        'trigger.callable' => function (Container $container): void {
            $container->delegate(new ReflectionContainer());
        },
    ]
];
