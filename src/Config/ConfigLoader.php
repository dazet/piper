<?php

namespace Piper\Config;

use Piper\Container\Services;
use Webmozart\Assert\Assert;

final class ConfigLoader
{
    /** @var ConfigParser[] */
    private $parsers = [];

    public function __construct(ConfigParser ...$parsers)
    {
        foreach ($parsers as $parser) {
            $key = $parser->key();
            Assert::keyNotExists($this->parsers, $key);
            $this->parsers[$key] = $parser;
        }
    }

    public function loadFile(ConfigFile $file): Services
    {
        $services = new Services();

        foreach ($file->block() as $key => $block) {
            $services = $services->join($this->parserFor($key)->parse($block));
        }

        return $services;
    }

    public function loadFiles(ConfigFile ...$files): Services
    {
        $services = new Services();

        foreach ($files as $file) {
            $services = $services->join($this->loadFile($file));
        }

        return $services;
    }

    private function parserFor(string $key): ConfigParser
    {
        Assert::keyExists($this->parsers, $key);

        return $this->parsers[$key];
    }
}
