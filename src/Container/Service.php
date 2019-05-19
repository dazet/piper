<?php

namespace Piper\Container;

use Webmozart\Assert\Assert;
use function array_merge;
use function array_unique;
use function is_object;

final class Service
{
    /** @var string */
    private $id;

    /** @var callable|null */
    private $factory;

    /** @var mixed|null */
    private $instance;

    /** @var bool */
    private $shared = true;

    /** @var string[] */
    private $tags = [];

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function fromFactory(string $id, callable $factory): self
    {
        $self = new self($id);
        $self->factory = $factory;

        return $self;
    }

    /**
     * @param mixed $instance
     */
    public static function fromInstance(string $id, $instance): self
    {
        $self = new self($id);
        $self->instance = $instance;
        $self->factory = function() use ($self) {
            return $self->shared || !is_object($self->instance) ? $self->instance : clone $self->instance;
        };

        return $self;
    }

    public static function fromClass(string $class): self
    {
        Assert::classExists($class);

        $self = new self($class);
        $self->factory = function() use ($class) {
            return new $class();
        };

        return $self;
    }

    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return mixed
     * @throws ServiceInstanceFailed
     */
    public function instance()
    {
        if (!$this->shared) {
            return $this->newInstance();
        }

        if ($this->instance === null) {
            $this->instance = $this->newInstance();
        }

        return $this->instance;
    }

    /**
     * @return string[]
     */
    public function tags(): array
    {
        return $this->tags;
    }

    public function withSharing(bool $shared = true): self
    {
        if ($this->shared === $shared) {
            return $this;
        }

        $copy = clone $this;
        $copy->shared = $shared;

        return $copy;
    }

    public function withTags(string ...$tags): self
    {
        $copy = clone $this;
        $copy->tags = array_unique(array_merge($this->tags, $tags));

        return $copy;
    }

    /**
     * @return mixed
     * @throws ServiceInstanceFailed
     */
    private function newInstance()
    {
        if ($this->factory !== null) {
            return ($this->factory)();
        }

        throw new ServiceInstanceFailed("Unable to initialize service {$this->id}");
    }
}
