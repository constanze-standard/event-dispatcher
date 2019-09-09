<?php

/*
 * This file is part of the beige-event package.
 *
 * (c) Alex <blldxt@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConstanzeStandard\EventDispatcher\Interfaces;

use Closure;

interface SubscriberInterface
{
    /**
     * Register the subscriber for each event.
     * 
     * @param Closure $subscriber [eventId => [mathod, priority]][]
     */
    public function subscribe(Closure $subscriber);
}
