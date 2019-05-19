<?php

namespace Piper\Config;

use LogicException;
use Piper\Pipeline\ObjectTags;
use Piper\Pipeline\Pipe;
use function get_class;
use function sprintf;

/**
 * Definition of pipe for configuration block parser.
 * It is tagged for ConfigBlock instances with proper parser key.
 */
final class ConfigParserPipe implements Pipe
{
    /** @var ConfigParser */
    private $parser;

    /** @var ObjectTags */
    private $input;

    /** @var int */
    private $order;

    /**
     * @param ConfigParser $parser
     */
    public function __construct(ConfigParser $parser, int $order = Pipe::NORMAL)
    {
        $this->parser = $parser;
        $this->input = ObjectTags::forClass(ConfigBlock::class, ['key' => $this->parser->key()]);
        $this->order = $order;
    }

    public function __invoke(object $object): ?object
    {
        if (!$object instanceof ConfigBlock) {
            throw new LogicException(sprintf('Expected ConfigBlock got %s', get_class($object)));
        }

        return $this->parser->parse($object);
    }

    public function input(): ObjectTags
    {
        return $this->input;
    }

    public function order(): int
    {
        return $this->order;
    }
}
