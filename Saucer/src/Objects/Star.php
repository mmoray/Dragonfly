<?php

namespace Saucer\Objects;

use DragonFly\Event\Event;
use DragonFly\Manager\DisplayManager;
use DragonFly\Manager\WorldManager;
use DragonFly\World\Color;
use DragonFly\World\WorldObject;
use DragonFly\World\Vector;

class Star extends WorldObject
{
    const CHAR_STAR = '*';
    const OBJECT_STAR = 'star';

    private $color;

    public function __construct(...$params)
    {
        parent::__construct(self::OBJECT_STAR);

        $this->color = new Color(Color::BACKGROUND[Color::BLACK], Color::FOREGROUND[Color::WHITE]);
        $this->setAltitude(0);
        $this->setPosition(new Vector(rand() % WorldManager::getInstance()->getView()->getHorizontal(), rand() % WorldManager::getInstance()->getView()->getVertical()));
        $this->setSolidness(self::SOLIDNESS_SPECTRAL);
        $this->setVelocity(new Vector(-1.0 / (rand() % 10 + 1), 0));
    }

    public function draw(): void
    {
        DisplayManager::getInstance()->drawChar($this->getPosition(), self::CHAR_STAR, $this->color);
    }

    public function handle(Event $event): bool
    {
        switch ($event->getType()) {
            case Event::EVENT_OUT:
                $this->setPosition(new Vector(WorldManager::getInstance()->getView()->getHorizontal() + rand() % 20, rand() % WorldManager::getInstance()->getView()->getVertical()));
                $this->setVelocity(new Vector(-1.0 / (rand() % 10 + 1), 0));
                return true;
            default:
                return parent::handle();
        }
    }
}
