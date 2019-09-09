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

use Psr\EventDispatcher\StoppableEventInterface;
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;

class Event implements EventInterface, StoppableEventInterface
{
    /**
     * Is propagation stopped?
     * 
     * @var bool
     */
    private $propagationStopped = false;

    /**
     * Get event name, default is class name.
     * 
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Set propagation status, set true if stopped or false.
     * 
     * @param bool $propagationStopped
     */
    public function propagationStopped(bool $propagationStopped)
    {
        $this->propagationStopped = $propagationStopped;
    }

    /**
     * Get a new event with stopped signal.
     * 
     * @return StoppableEventInterface
     */
    public function withPropagationStopped(): StoppableEventInterface
    {
        $event = clone $this;
        $event->propagationStopped(true);
        return $event;
    }

    /**
     * Is propagation stopped?
     * 
     * @return bool
     */
    public function isPropagationStopped() : bool
    {
        return $this->propagationStopped;
    }
}
