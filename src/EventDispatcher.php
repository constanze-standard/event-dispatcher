<?php

/*
 * This file is part of the beige-event package.
 *
 * (c) Alex <blldxt@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConstanzeStandard\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * Listener provider service.
     * 
     * @var ListenerProviderInterface
     */
    private $listenerProvider;

    /**
     * Set the listener provider.
     * 
     * @param ListenerProviderInterface $listenerProvider
     */
    public function __construct(ListenerProviderInterface $listenerProvider)
    {
        $this->listenerProvider = $listenerProvider;
    }

    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param object $event
     *   The object to process.
     *
     * @return object
     *   The Event that was passed, now modified by listeners.
     */
    public function dispatch(object $event)
    {
        if ($this->isEventPropagationStopped($event)) {
            return $event;
        }

        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            $returnedEvent = $listener($event);

            if (empty($returnedEvent) || (!$returnedEvent instanceof $event)) {
                throw new \RuntimeException('Listener must return an event that instanceof it self.');
            }

            if ($this->isEventPropagationStopped($event)) {
                break;
            }
        }
        return $event;
    }

    /**
     * If the event propagation stiooed?
     * 
     * @param object $event
     * 
     * @return bool
     */
    private function isEventPropagationStopped(object $event): bool
    {
        return (
            $event instanceof StoppableEventInterface &&
            $event->isPropagationStopped()
        );
    }
}
