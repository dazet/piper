<?php

namespace Piper\Container;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class Container implements ContainerInterface
{
    /** @var Service[] */
    private $servicesById = [];

    /** @var Service[][] */
    private $servicesByTag = [];

    public function __construct()
    {
        $this->add(Service::fromInstance(self::class, $this));
        $this->add(Service::fromInstance(ContainerInterface::class, $this));
    }

    public function add(Service $service): void
    {
        $this->servicesById[$service->id()] = $service;

        foreach (array_keys($this->servicesByTag) as $tag) {
            unset($this->servicesByTag[$tag][$service->id()]);
        }

        foreach ($service->tags() as $tag) {
            $this->servicesByTag[$tag][] = $service;
        }
    }

    public function addServices(Services $services): void
    {
        foreach ($services->all() as $service) {
            $this->add($service);
        }
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ServiceInstanceFailed
     * @throws ServiceNotFound
     */
    public function get($id)
    {
        self::assertValidId($id);

        if ($this->hasService($id)) {
            return $this->servicesById[$id]->instance();
        }

        if ($this->hasTag($id)) {
            return array_map(
                function (Service $service) {
                    return $service->instance();
                },
                $this->servicesByTag[$id]
            );
        }

        throw new ServiceNotFound("Service {$id} not found");
    }

    /**
     * @inheritdoc
     */
    public function has($id): bool
    {
        if (!is_string($id)) {
            return false;
        }

        return $this->hasService($id) || $this->hasTag($id);
    }

    private static function assertValidId($id): void
    {
        Assert::string($id);
        Assert::notEmpty($id);
    }

    private function hasService($id): bool
    {
        return array_key_exists($id, $this->servicesById);
    }

    private function hasTag($id): bool
    {
        return array_key_exists($id, $this->servicesByTag);
    }
}
