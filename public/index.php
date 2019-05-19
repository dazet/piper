<?php

require __DIR__ . '/../vendor/autoload.php';

use Piper\Config\ConfigFile;
use Piper\Container\Container;
use Piper\Http\AppKernel;
use Piper\Http\StartRequest;
use Piper\Pipeline\Pipeline;

$app = new AppKernel(new Container());
$app->loadConfig(
    new ConfigFile(require __DIR__ . '/../etc/boot.php'),
    new ConfigFile(require __DIR__ . '/../etc/http.php')
);

$httpPipeline = new Pipeline($app->get('http.tagger'), ...$app->get('pipeline:http'));
$httpPipeline->pump(new StartRequest());
