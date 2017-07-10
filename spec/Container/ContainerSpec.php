<?php 

namespace spec\Piper\Container;

use PhpSpec\ObjectBehavior;
use Piper\Container\Container;
use Piper\Container\Service;
use Psr\Container\ContainerInterface;
use spec\Piper\Stub;

final class ContainerSpec extends ObjectBehavior 
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_implements_PSR_ContainerInterface()
    {
        $this->shouldImplement(ContainerInterface::class);
    }

    function it_returns_self_when_asked_for_ContainerInterface()
    {
        $this->get(ContainerInterface::class)->shouldReturn($this);
    }

    function it_returns_self_when_asked_for_self()
    {
        $this->get(Container::class)->shouldReturn($this);
    }

    function it_returns_service_instance()
    {
        $instance = new Stub\A();
        $service = Service::fromInstance('A', $instance);

        $this->add($service);

        $this->has('A')->shouldBe(true);
        $this->get('A')->shouldReturn($instance);
    }

    function it_returns_tagged_service_instances()
    {
        $a = new Stub\A();
        $b = new Stub\B();
        $c = new Stub\C();
        $serviceA = Service::fromInstance('A', $a)->withTags('yes');
        $serviceB = Service::fromInstance('B', $b)->withTags('yes');
        $serviceC = Service::fromInstance('C', $c)->withTags('nope');

        $this->add($serviceA);
        $this->add($serviceB);
        $this->add($serviceC);

        $this->get('yes')->shouldReturn([$a, $b]);
    }
}
