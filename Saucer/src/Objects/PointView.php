<?php

namespace Saucer\Objects;

use DragonFly\Event\Event;
use DragonFly\Event\KeyboardEvent;
use DragonFly\World\Color;
use DragonFly\World\ViewObject;

class PointView extends ViewObject
{
    const VIEW_STRING = 'Points:';

    public function __construct(...$params)
    {
        parent::__construct();

        $this->getColor()->setForeground(Color::FOREGROUND[Color::YELLOW]);
        $this->registerInterest(Event::EVENT_STEP);
        $this->setLocation(self::LOCATION_TOP_RIGHT);
        $this->setViewString(self::VIEW_STRING);
    }

    public function handle(Event $event): bool
    {
        switch ($event->getType()) {
            case Event::EVENT_STEP:
                if ($event->getStepCount() % 30 === 0) {
                    $this->setValue($this->getValue() + 1);
                }
                return true;
            default:
                return parent::handle($event);
        }
    }
}
