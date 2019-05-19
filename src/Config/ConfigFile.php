<?php

namespace Piper\Config;

use Webmozart\Assert\Assert;
use function array_keys;
use function array_map;

/**
 * Configuration file structure containing one or many configuration blocks:
 *
 * $blocks = [
 *     'block_key' => [ ... block config ... ]
 * ];
 */
final class ConfigFile
{
    /** @var ConfigBlock[] */
    private $blocks;

    public function __construct(array $content)
    {
        $keys = array_keys($content);
        Assert::allString($keys);
        Assert::allNotEmpty($keys);

        $this->blocks = array_map(
            function(string $key, array $config): ConfigBlock {
                return new ConfigBlock($key, $config);
            },
            $keys,
            $content
        );
    }

    /**
     * @return ConfigBlock[]
     */
    public function blocks(): array
    {
        return $this->blocks;
    }
}
