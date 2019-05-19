<?php

namespace Piper\Config;

use Webmozart\Assert\Assert;

/**
 * Configuration file block.
 */
final class ConfigBlock
{
    /**
     * Configuration block key allowing to choose right ConfigParser.
     * @see ConfigParser
     * @var string
     */
    private $key;

    /**
     * Configuration content to parse.
     * @var array
     */
    private $content;

    public function __construct(string $key, array $content)
    {
        Assert::notEmpty($key);
        $this->key = $key;
        $this->content = $content;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function content(): array
    {
        return $this->content;
    }
}
