<?php

namespace Piper\Pipeline;

use InvalidArgumentException;
use JsonSerializable;
use Piper\Pipeline\Util\ArrayUtil;
use function array_map;
use function ksort;
use function trim;

final class ObjectTag implements JsonSerializable
{
    /** @var string */
    private $class;

    /** @var string[] */
    private $attributes;

    public function __construct(string $class, array $attributes = [])
    {
        $class = trim($class);
        if ($class === '') {
            throw new InvalidArgumentException('Empty class identifier');
        }

        $this->class = $class;
        $this->attributes = array_map('\strval', $attributes);
        ksort($this->attributes);
    }

    public static function fromClass(string $class): self
    {
        return new self($class);
    }

    public function class(): string
    {
        return $this->class;
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): array
    {
        return ['class' => $this->class, 'attributes' => $this->attributes];
    }

    public function toString(): string
    {
        return ArrayUtil::jsonEncode($this->jsonSerialize());
    }
}
