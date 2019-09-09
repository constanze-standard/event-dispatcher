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

interface EventInterface
{
    /**
     * Get the identification name for event.
     * 
     * @return string
     */
    public function getName(): string;
}
