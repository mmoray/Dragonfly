<?php

namespace Saucer\Objects;

use DragonFly\Event\CollisionEvent;
use DragonFly\Event\Event;
use DragonFly\Event\ViewEvent;
use DragonFly\Manager\WorldManager;
use DragonFly\World\Vector;
use DragonFly\World\WorldObject;
use Saucer\Events\NukeEvent;

class Saucer extends WorldObject
{
    const OBJECT_SAUCER = 'saucer';
    const SPRITE_PATH = __DIR__ . '/../Sprites/Saucer.txt';

    private $points;

    /**
     * Instantiate class and set properties.
     */
    public function __construct(...$params)
    {
        parent::__construct(self::OBJECT_SAUCER);

        $this->points = false;
        $this->registerInterest(NukeEvent::EVENT_NUKE);
        $this->setSprite(self::OBJECT_SAUCER);
        $this->setVelocity(new Vector(-0.25, 0));
        $this->moveToStart();
    }

    public function __destruct() 
    {
        parent::__destruct();
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
            case NukeEvent::EVENT_NUKE:
                new Explosion($this->position->getX(), $this->position->getY());
                new Saucer;
                WorldManager::getInstance()->markForDelete($this);
                WorldManager::getInstance()->dispatch(new ViewEvent(PointView::VIEW_STRING, 10, true));
                return true;
            default:
                return parent::handle($event);
        }
    }

    private function hit(CollisionEvent $event): void
    {
        $offended = $event->getOffended();
        $offending = $event->getOffending();
        if ($offended->getType() === Bullet::OBJECT_BULLET || $offending->getType() === Bullet::OBJECT_BULLET) {
            new Explosion($this->position->getX(), $this->position->getY());
            new Saucer;
            if (!$this->points) {
                $this->points = true;
                WorldManager::getInstance()->dispatch(new ViewEvent(PointView::VIEW_STRING, 10, true));
            }
        }
        if ($offended->getType() === Hero::OBJECT_HERO || $offending->getType() === Hero::OBJECT_HERO) {
            WorldManager::getInstance()->markForDelete($offended);
            WorldManager::getInstance()->markForDelete($offending);
        }
    }

    private function moveToStart(): void
    {
        $wm = WorldManager::getInstance();
        $position = new Vector($wm->getView()->getHorizontal() + rand() % $wm->getView()->getHorizontal() + 3, rand() % $wm->getView()->getVertical());
        $collisions = $wm->getCollisions($this, $position);
        while (!$collisions->isEmpty()) {
            $position->setX($position->getX() + 1);
            $collisions = $wm->getCollisions($this, $position);
        }
        $this->setPosition($position);
    }

    private function out(): void
    {
        if ($this->position->getX() <= 0) {
            $this->moveToStart();
            new Saucer;
        }
    }
}
