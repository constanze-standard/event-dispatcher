<?php
use ConstanzeStandard\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\ListenerProviderInterface;
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;
use ConstanzeStandard\EventDispatcher\Event;
use Psr\EventDispatcher\StoppableEventInterface;

require_once __DIR__ . '/AbstractTest.php';

class Tester
{
    public function __invoke($event)
    {
        $event->propagationStopped(true);
        return $event;
    }
}

class DispatcherTest extends AbstractTest
{
    public function testConstructor()
    {
        /** @var ListenerProviderInterface $listenerProvider */
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $dispatcher = new EventDispatcher($listenerProvider);
        $listenerProviderProperty = $this->getProperty($dispatcher, 'listenerProvider');
        $this->assertInstanceOf(ListenerProviderInterface::class, $listenerProviderProperty);
        $this->assertEquals($listenerProviderProperty, $listenerProvider);
    }

    public function testDispatchEventPropagationStopped()
    {
        /** @var ListenerProviderInterface $listenerProvider */
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $dispatcher = new EventDispatcher($listenerProvider);
        /** @var StoppableEventInterface $event */
        $event = $this->createMock(StoppableEventInterface::class);
        $event->expects($this->once())->method('isPropagationStopped')->willReturn(true);
        $result = $dispatcher->dispatch($event);
        $this->assertEquals($result, $event);
    }

    public function testDispatchNotEventPropagationStopped()
    {
        /** @var StoppableEventInterface $event */
        $event = $this->createMock(StoppableEventInterface::class);
        $event->expects($this->exactly(2))->method('isPropagationStopped')->willReturn(false);
        $listener = $this->createMock(Tester::class);
        $listener->expects($this->once())->method('__invoke')->willReturn($event);

        /** @var ListenerProviderInterface $listenerProvider */
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $listenerProvider->expects($this->once())->method('getListenersForEvent')->willReturn([
            $listener
        ]);
        $dispatcher = new EventDispatcher($listenerProvider);
        $result = $dispatcher->dispatch($event);
        $this->assertEquals($result, $event);
    }

    public function testDispatchHalfwayEventPropagationStopped()
    {
        $event = new Event();
        $listener = new Tester();
        $listener2 = $this->createMock(Tester::class);
        $listener2->expects($this->exactly(0))->method('__invoke')->willReturn($event);
        /** @var ListenerProviderInterface $listenerProvider */
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $listenerProvider->expects($this->once())->method('getListenersForEvent')->willReturn([
            $listener, $listener2
        ]);
        $dispatcher = new EventDispatcher($listenerProvider);
        /** @var StoppableEventInterface $event */
        $result = $dispatcher->dispatch($event);
        $this->assertEquals($result, $event);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDispatchException()
    {
        /** @var StoppableEventInterface $event */
        $event = $this->createMock(StoppableEventInterface::class);
        $event->expects($this->exactly(1))->method('isPropagationStopped')->willReturn(false);
        $listener = $this->createMock(Tester::class);
        $listener->expects($this->once())->method('__invoke')->willReturn(null);

        /** @var ListenerProviderInterface $listenerProvider */
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $listenerProvider->expects($this->once())->method('getListenersForEvent')->willReturn([
            $listener
        ]);
        $dispatcher = new EventDispatcher($listenerProvider);
        $result = $dispatcher->dispatch($event);
    }
}
