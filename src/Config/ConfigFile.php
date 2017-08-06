<?php

namespace Piper\Config;

use Webmozart\Assert\Assert;

final class ConfigFile
{
    /** @var array */
    private $content;

    public function __construct(array $content)
    {
        $this->content = $content;
    }

    public static function fromPath(string $path): self
    {
        Assert::fileExists($path);

        return new self(file($path));
    }

    public function nextBlock(): iterable
    {
        foreach ($this->content as $parser => $config) {
            yield $parser => $config;
        }
    }
}
