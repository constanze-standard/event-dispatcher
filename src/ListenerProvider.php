<?php

/*
 * This file is part of the constanze-standard/event-dispatcher package.
 *
 * (c) Alex <blldxt@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConstanzeStandard\EventDispatcher;

use Closure;
use InvalidArgumentException;
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;
use ConstanzeStandard\EventDispatcher\Interfaces\SubscriberInterface;
use ConstanzeStandard\EventDispatcher\Interfaces\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * Listeners with priority.
     * 
     * @var array
     */
    protected $listeners = [];

    /**
     * Add A listener.
     * 
     * @param string $key
     * @param callback|array $listener
     */
    public function addListener(string $key, $listener, int $priority = 0)
    {
        if (! array_key_exists($priority, $this->listeners)) {
            $this->listeners[$priority] = [];
        }
        if (! array_key_exists($key, $this->listeners[$priority])) {
            $this->listeners[$priority][$key] = [];
        }
        $this->listeners[$priority][$key][] = $listener;
    }

    /**
     * Add a subscriber.
     * 
     * @param SubscriberInterface $subscriber
     * @param int $priority
     */
    public function addSubscriber(SubscriberInterface $subscriber)
    {
        $closure = $this->getSubscriberRegister($subscriber);
        $subscriber->subscribe($closure);
    }

    /**
     * Get listeners from event's name.
     * 
     * @param object $event
     * 
     * @throws \TypeError
     * 
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof EventInterface === false) {
            throw new \TypeError('The named event must implement \ConstanzeStandard\EventDispatcherDispatcher\Interfaces\EventInterface.');
        }

        $eventId = $event->getName();
        krsort($this->listeners, SORT_NUMERIC);
        foreach ($this->listeners as $listeners) {
            if (isset($listeners[$eventId])) {
                yield from $listeners[$eventId];
            }
        }
    }

    /**
     * Get the subscriber register closure.
     * 
     * @param SubscriberInterface $subscriber
     * 
     * @throws \InvalidArgumentException
     * 
     * @return Closure
     */
    private function getSubscriberRegister(SubscriberInterface $subscriber): Closure
    {
        $closure = function ($id, ...$methods) use ($subscriber) {
            foreach ($methods as $value) {
                switch (true) {
                    case is_string($value):
                        $method = $value;
                        $priority = 0;
                        break;
                    case is_array($value):
                        $method = $value[0];
                        $priority = $value[1] ?? 0;
                        break;
                    default:
                        throw new InvalidArgumentException('method parameters only accepts string or array.');
                }

                $this->addListener($id, [$subscriber, $method], $priority);
            }
        };
        return $closure->bindTo($this);
    }
}
