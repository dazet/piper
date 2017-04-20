<?php

namespace Piper\Pipe;

use Webmozart\Assert\Assert;

final class ObjectTag implements \JsonSerializable
{
    /** @var string */
    private $class;

    /** @var string[] */
    private $attributes;

    public function __construct(string $class, array $attributes = [])
    {
        Assert::notEmpty($class);
        Assert::allString($attributes);

        $this->class = $class;
        $this->attributes = $attributes;
    }

    public function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        Assert::isArray($data);
        Assert::keyExists($data, 'class');

        return new self($data['class'], $data['attributes'] ?? []);
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
        return json_encode($this->jsonSerialize());
    }
}
