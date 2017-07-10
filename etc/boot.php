<?php

use Piper\Http\Routing\RoutedRequestTagger;
use Piper\Pipe\ObjectTagger;
use Piper\Pipe\ObjectTagger\ClassTagger;
use Piper\Pipe\ObjectTagger\InterfacesTagger;
use Piper\Pipe\ObjectTagger\TaggersAggregate;

return [
    'services' => [
        ObjectTagger::class => function() {
            return new TaggersAggregate(
                new ClassTagger(), new InterfacesTagger(), new RoutedRequestTagger()
            );
        }
    ],
];
