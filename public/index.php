<?php

require __DIR__ . '/../vendor/autoload.php';

use Piper\Config\ConfigFile;
use Piper\Container\Container;
use Piper\Http\StartRequest;
use Piper\Kernel;
use Piper\Pipe\ObjectTagger;
use Piper\Pipeline;

$app = new Kernel(new Container());
$app->loadConfig(
    new ConfigFile(require __DIR__ . '/../etc/boot.php'),
    new ConfigFile(require __DIR__ . '/../etc/http.php')
);

$httpPipeline = new Pipeline($app->get(ObjectTagger::class), ...$app->get('pipeline:http'));
$httpPipeline->pump(new StartRequest());
