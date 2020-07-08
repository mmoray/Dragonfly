<?php

namespace Saucer\Objects;

use DragonFly\Event\KeyboardEvent;
use DragonFly\Event\Event;
use DragonFly\Event\ViewEvent;
use DragonFly\Manager\LogManager;
use DragonFly\Manager\GameManager;
use DragonFly\Manager\WorldManager;
use DragonFly\World\Vector;
use DragonFly\World\WorldObject;
use Saucer\Events\NukeEvent;

class Hero extends WorldObject
{
    const OBJECT_HERO = 'hero';
    const SPRITE_PATH = __DIR__ . '/../Sprites/Hero.txt';

    private $fireCountdown;
    private $fireSlowdown;
    private $moveCountdown;
    private $moveSlowdown;
    private $nukeCount;

    public function __construct(...$params)
    {
        parent::__construct(self::OBJECT_HERO, 7., WorldManager::getInstance()->getView()->getVertical() / 2);

        $this->registerInterest(Event::EVENT_KEYBOARD);
        $this->registerInterest(Event::EVENT_STEP);
        $this->setSprite(self::OBJECT_HERO);
        $this->moveCountdown = $this->moveSlowdown = 2;
        $this->fireCountdown = $this->fireSlowdown = 15;
        $this->nukeCount = 1;

        LogManager::getInstance()->info("Hero solid: %s", $this->isSolid() ? "true" : "false");
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
        switch($event->getType()) {
            case Event::EVENT_COLLISION:
                if ($event->getOffended()->getType() === Saucer::OBJECT_SAUCER || $event->getOffending()->getType() === Saucer::OBJECT_SAUCER) {
                    GameManager::getInstance()->setGameOver();
                }
                return true;
            case Event::EVENT_KEYBOARD:
                $this->keyboard($event->getValue());
                return true;
            case Event::EVENT_STEP:
                $this->fireCountdown--;
                if ($this->fireCountdown < 0) {
                    $this->fireCountdown = 0;
                }
                $this->moveCountdown--;
                if ($this->moveCountdown < 0) {
                    $this->moveCountdown = 0;
                }
                return true;
            default:
                return parent::handle($event);
        }
    }

    private function keyboard(int $keyCode): void
    {
        switch($keyCode) {
            case KeyboardEvent::KEY_LOWER_Q:
            case KeyboardEvent::KEY_UPPER_Q:
                GameManager::getInstance()->setGameOver();
                break;
            case KeyboardEvent::KEY_LOWER_W:
            case KeyboardEvent::KEY_UPPER_W:
                $this->move(-1);
                break;
            case KeyboardEvent::KEY_LOWER_S:
            case KeyboardEvent::KEY_UPPER_S:
                $this->move(1);
                break;
            case KeyboardEvent::KEY_LOWER_E:
            case KeyboardEvent::KEY_UPPER_E:
                $this->fireBullet();
                break;
            case KeyboardEvent::KEY_SPACE:
                $this->nuke();
                break;
            default:
                break;
        }
    }

    private function fireBullet(): void
    {
        if ($this->fireCountdown === 0) {
            $bullet = new Bullet($this->position->getX() + 3, $this->position->getY());
            $bullet->setDirection(new Vector(1, 0));
        }
    }

    private function move(int $direction): void
    {
        if ($this->moveCountdown === 0) {
            $this->moveCountdown = $this->moveSlowdown;
            $newPosition = new Vector($this->position->getX(), $this->position->getY() + $direction);
            $wm = WorldManager::getInstance();
            if ($newPosition->getY() > 3 && $newPosition->getY() < $wm->getView()->getVertical() - 1) {
                $wm->moveObject($this, $newPosition);
            }
        }
    }

    private function nuke(): void
    {
        if ($this->nukeCount > 0) {
            $this->nukeCount--;
            WorldManager::getInstance()->dispatch(new NukeEvent);
            WorldManager::getInstance()->dispatch(new ViewEvent(NukeView::VIEW_STRING, -1, true));
        }
    }
}
