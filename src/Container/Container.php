<?php

namespace Piper\Container;

use Piper\Pipeline\Util\ArrayUtil;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;
use function is_string;

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
        $this->removeServiceTags($service);
        $this->addServiceTags($service);
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
     * @param string $id
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

    private function addServiceTags(Service $service): void
    {
        foreach ($service->tags() as $tag) {
            $this->servicesByTag[$tag][] = $service;
        }
    }

    private function removeServiceTags(Service $service): void
    {
        foreach ($this->servicesByTag as $tag => $services) {
            $this->servicesByTag[$tag] = ArrayUtil::except($services, $service->id());
        }
    }
}
