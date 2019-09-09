<?php
use ConstanzeStandard\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\ListenerProviderInterface;
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;
use ConstanzeStandard\EventDispatcher\Event;
use Psr\EventDispatcher\StoppableEventInterface;
use ConstanzeStandard\EventDispatcher\ListenerProvider;
use ConstanzeStandard\EventDispatcher\Interfaces\SubscriberInterface;

require_once __DIR__ . '/AbstractTest.php';

class Subscriber implements SubscriberInterface
{
    public function subscribe(Closure $subscriber)
    {
        $subscriber(
            'test',
            ['onCreate', 2]
        );
    }

    public function onCreate($event)
    {
        echo $event->getData();
    }
}

class Subscriber2 implements SubscriberInterface
{
    public function subscribe(Closure $subscriber)
    {
        $subscriber('test', 'onCreate');
    }

    public function onCreate($event)
    {
        echo $event->getData();
    }
}

class Subscriber3 implements SubscriberInterface
{
    public function subscribe(Closure $subscriber)
    {
        $obj = new stdClass();
        $subscriber('test', $obj);
    }
}


class ListenerProviderTest extends AbstractTest
{
    public function testAddListenerEmpty()
    {
        $key = 'test';
        $listener = 1;
        $priority = 1;
        $listenerProvider = new ListenerProvider();
        $listenerProvider->addListener($key, $listener, $priority);
        $listeners = $this->getProperty($listenerProvider, 'listeners');
        $this->assertEquals($listeners, [
            $priority => [
                $key => [$listener]
            ]
        ]);
    }

    public function testAddListenerExist()
    {
        $key = 'test';
        $listener = 1;
        $priority = 1;
        $listenerProvider = new ListenerProvider();
        $this->setProperty($listenerProvider, 'listeners', [
            $priority => [
                $key => [0]
            ]
        ]);
        $listenerProvider->addListener($key, $listener, $priority);
        $listeners = $this->getProperty($listenerProvider, 'listeners');
        $this->assertEquals($listeners, [
            $priority => [
                $key => [0, $listener]
            ]
        ]);
    }

    public function testAddSubscriberEmpty()
    {
        /** @var SubscriberInterface $subscriber */
        $subscriber = new Subscriber();
        $listenerProvider = new ListenerProvider();
        $listenerProvider->addSubscriber($subscriber);
        $listeners = $this->getProperty($listenerProvider, 'listeners');
        $this->assertEquals($listeners, [
            2 => [
                'test' => [[$subscriber, 'onCreate']]
            ]
        ]);
    }

    public function testAddSubscriberWithStringParam()
    {
        /** @var SubscriberInterface $subscriber */
        $subscriber = new Subscriber2();
        $listenerProvider = new ListenerProvider();
        $listenerProvider->addSubscriber($subscriber);
        $listeners = $this->getProperty($listenerProvider, 'listeners');
        $this->assertEquals($listeners, [
            0 => [
                'test' => [[$subscriber, 'onCreate']]
            ]
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddSubscriberInvalidArgumentException()
    {
        $subscriber = new Subscriber3();
        $listenerProvider = new ListenerProvider();
        $listenerProvider->addSubscriber($subscriber);
    }

    public function testAddSubscriberExist()
    {
        /** @var SubscriberInterface $subscriber */
        $subscriber0 = new Subscriber();
        $subscriber = new Subscriber();
        $listenerProvider = new ListenerProvider();
        $this->setProperty($listenerProvider, 'listeners', [
            2 => [
                'test' => [[$subscriber0, 'onCreate']]
            ]
        ]);
        $listenerProvider->addSubscriber($subscriber);
        $listeners = $this->getProperty($listenerProvider, 'listeners');
        $this->assertEquals($listeners, [
            2 => [
                'test' => [
                    [$subscriber0, 'onCreate'],
                    [$subscriber, 'onCreate']
                ]
            ]
        ]);
    }

    /**
     * @expectedException \TypeError
     */
    public function testGetListenersForEventWithError()
    {
        $listenerProvider = new ListenerProvider();
        $obj = new \stdClass();
        foreach($listenerProvider->getListenersForEvent($obj) as $a) {

        }
    }

    public function testGetListenersForEventReturnIterable()
    {
        $key = 'test';
        $listener = 1;
        $priority = 1;

        $event = $this->createMock(EventInterface::class);
        $event->expects($this->once())->method('getName')->willReturn($key);
        $listenerProvider = new ListenerProvider();
        $this->setProperty($listenerProvider, 'listeners', [
            $priority => [
                $key => [$listener]
            ]
        ]);
        foreach($listenerProvider->getListenersForEvent($event) as $listener) {
            $this->assertEquals(1, $listener);
        }
    }
}
