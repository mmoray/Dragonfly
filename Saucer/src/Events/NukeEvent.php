<?php

namespace Saucer\Events;

use DragonFly\Event\Event;

class NukeEvent extends Event
{
    const EVENT_NUKE = 'nuke';

    public function __construct(...$params)
    {
        parent::__construct(self::EVENT_NUKE);
    }
}
