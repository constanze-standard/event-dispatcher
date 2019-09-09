<?php
use ConstanzeStandard\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\ListenerProviderInterface;
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;
use ConstanzeStandard\EventDispatcher\Event;
use Psr\EventDispatcher\StoppableEventInterface;

require_once __DIR__ . '/AbstractTest.php';

class EventTest extends AbstractTest
{
    public function testGetIdDefault()
    {
        $event = new Event();
        $id = $event->getName();
        $this->assertEquals(Event::class, $id);
    }


    public function testPropagationStopped()
    {
        $event = new Event();
        $this->setProperty($event, 'propagationStopped', false);
        $event->propagationStopped(true);
        $propagationStopped = $this->getProperty($event, 'propagationStopped');
        $this->assertTrue($propagationStopped);
    }

    public function testIsPropagationStopped()
    {
        $event = new Event();
        $this->setProperty($event, 'propagationStopped', true);
        $propagationStopped = $event->isPropagationStopped();
        $this->assertTrue($propagationStopped);
    }

    public function testWithPropagationStopped()
    {
        $event = new Event();
        $result = $event->withPropagationStopped();
        $propagationStopped = $this->getProperty($result, 'propagationStopped');
        $this->assertTrue($propagationStopped);
    }
}
