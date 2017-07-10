<?php

namespace Piper\Container;

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

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @param string $id
     * @param callable $factory
     * @return Service
     */
    public static function fromFactory(string $id, callable $factory): self
    {
        $self = new self($id);
        $self->factory = $factory;

        return $self;
    }

    /**
     * @param string $id
     * @param mixed $instance
     * @return Service
     */
    public static function fromInstance(string $id, $instance): self
    {
        $self = new self($id);
        $self->instance = $instance;
        $self->factory = function() use ($self) {
            return $self->shared ? $self->instance : clone $self->instance;
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
            return ($this->factory)();
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
        $copy->tags = array_keys(array_count_values(array_merge($this->tags, $tags)));

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

        if (class_exists($this->id)) {
            $class = $this->id;

            return new $class();
        }

        throw new ServiceInstanceFailed("Unable to initialize service {$this->id}");
    }
}
