<?php

namespace DragonFly\Event;

class OutEvent extends Event
{
    /**
     * Instantiate class and properties.
     */
    public function __construct(...$params)
    {
        parent::__construct(self::EVENT_OUT);
    }
}
