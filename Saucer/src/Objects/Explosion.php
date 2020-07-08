<?php

namespace Saucer\Objects;

use DragonFly\Event\Event;
use DragonFly\Manager\WorldManager;
use DragonFly\World\WorldObject;

class Explosion extends WorldObject
{
    const OBJECT_EXPLOSION = 'explosion';
    const SPRITE_PATH = __DIR__ . '/../Sprites/Explosion.txt';

    private $timeToLive = 0;

    public function __construct(...$params)
    {
        $x = isset($params[0]) && (is_float($params[0]) || is_numeric($params[0])) ? $params[0] : 0;
        $y = isset($params[1]) && (is_float($params[1]) || is_numeric($params[1])) ? $params[1] : 0;
        parent::__construct(self::OBJECT_EXPLOSION, $x, $y);

        $this->registerInterest(Event::EVENT_STEP);
        $this->setSolidness(self::SOLIDNESS_SPECTRAL);
        $this->setSprite(self::OBJECT_EXPLOSION);
        $this->timeToLive = $this->animation->getSprite()->getFrameCount();
    }

    /**
     * Handle event (default is to ignore everything).
     *
     * @param Event $event
     * @return boolean Return false if ignored, else true if handled.
     */
    public function handle(Event $event): bool
    {
        switch($event->getType()) {
            case Event::EVENT_STEP:
                $this->step();
                return true;
            default:
                return parent::handle($event);
        }
    }

    private function step(): void
    {
        $this->timeToLive--;
        if ($this->timeToLive === 0) {
            WorldManager::getInstance()->markForDelete($this);
        }
    }
}
