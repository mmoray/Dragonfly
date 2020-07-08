<?php

namespace Saucer\Objects;

use DragonFly\Event\CollisionEvent;
use DragonFly\Event\Event;
use DragonFly\Manager\LogManager;
use DragonFly\Manager\WorldManager;
use DragonFly\World\WorldObject;

class Bullet extends WorldObject
{
    const OBJECT_BULLET = 'bullet';
    const SPRITE_PATH = __DIR__ . '/../Sprites/Bullet.txt';

    public function __construct(...$params)
    {
        $x = isset($params[0]) && (is_float($params[0]) || is_numeric($params[0])) ? $params[0] : 0;
        $y = isset($params[1]) && (is_float($params[1]) || is_numeric($params[1])) ? $params[1] : 0;
        parent::__construct(self::OBJECT_BULLET, $x, $y);
        $this->setSpeed(1);

        $this->registerInterest(Event::EVENT_COLLISION);
        $this->registerInterest(Event::EVENT_OUT);
        $this->setSolidness(self::SOLIDNESS_SOFT);
        $this->setSprite(self::OBJECT_BULLET);
    }

    /**
     * Handle event (default is to ignore everything).
     *
     * @param Event $event
     * @return boolean Return false if ignored, else true if handled.
     */
    public function handle(Event $event): bool
    {
        switch ($event->getType()) {
            case Event::EVENT_COLLISION:
                $this->hit($event);
                return true;
            case Event::EVENT_OUT:
                $this->out();
                return true;
            default:
                return parent::handle($event);
        }
    }

    private function hit(CollisionEvent $event): void
    {
        $offended = $event->getOffended();
        $offending = $event->getOffending();
        if ($offended->getType() === Saucer::OBJECT_SAUCER || $offending->getType() === Saucer::OBJECT_SAUCER) {
            $wm = WorldManager::getInstance();
            $wm->markForDelete($offended);
            $wm->markForDelete($offending);
        }
    }

    private function out(): void
    {
        WorldManager::getInstance()->markForDelete($this);
    }
}
