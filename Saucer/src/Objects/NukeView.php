<?php

namespace Saucer\Objects;

use DragonFly\Event\Event;
use DragonFly\Event\KeyboardEvent;
use DragonFly\World\Color;
use DragonFly\World\ViewObject;

class NukeView extends ViewObject
{
    const VIEW_STRING = 'Nukes:';

    public function __construct(...$params)
    {
        parent::__construct();

        $this->getColor()->setForeground(Color::FOREGROUND[Color::YELLOW]);
        $this->setLocation(self::LOCATION_TOP_LEFT);
        $this->setValue(1);
        $this->setViewString(self::VIEW_STRING);
    }
}
