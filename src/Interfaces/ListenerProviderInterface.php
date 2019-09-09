<?php

/*
 * This file is part of the constanze-standard/event-dispatcher package.
 *
 * (c) Alex <blldxt@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConstanzeStandard\EventDispatcher\Interfaces;

use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProviderInterface;

interface ListenerProviderInterface extends PsrListenerProviderInterface
{
    /**
     * Add A listener.
     * 
     * @param string $key
     * @param callback|array $listener
     */
    public function addListener(string $key, $listener, int $priority = 0);

    /**
     * Add a subscriber.
     * 
     * @param SubscriberInterface $subscriber
     * @param int $priority
     */
    public function addSubscriber(SubscriberInterface $subscriber);
}
