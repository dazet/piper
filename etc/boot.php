<?php

use Piper\Pipeline\ObjectTag\ClassTagger;
use Piper\Pipeline\ObjectTag\InterfacesTagger;
use Piper\Pipeline\ObjectTag\TaggersAggregate;
use Piper\Pipeline\ObjectTagger;

return [
    'services' => [
        ObjectTagger::class => function() {
            return new TaggersAggregate(new ClassTagger(), new InterfacesTagger());
        }
    ],
];
