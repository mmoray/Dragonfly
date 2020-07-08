<?php

namespace Transistor\Objects;

use DragonFly\Event\Event;
use DragonFly\Event\KeyboardEvent;
use DragonFly\Manager\DisplayManager;
use DragonFly\Manager\GameManager;
use DragonFly\Manager\LogManager;
use DragonFly\Manager\WorldManager;
use DragonFly\World\Color;
use DragonFly\World\Vector;
use DragonFly\World\WorldObject;

class Breadboard extends WorldObject
{
    const OBJECT_BREADBOARD = 'breadboard';

    private $negative;
    private $positive;
    private $transistors;

    public function __construct(...$params)
    {
        $position = WorldManager::getInstance()->getView()->getCorner();
        parent::__construct(self::OBJECT_BREADBOARD, $position->getX(), $position->getY());

        $this->transistors = [];
        for ($i = 1; $i <= WorldManager::getInstance()->getView()->getVertical(); $i++) {
            $this->negative[] = new Wire($this->position->getX() + 1, $this->position->getY() + $i);
            $this->positive[] = new Wire($this->position->getX() + 2, $this->position->getY() + $i, true);
        }
        $this->registerInterest(Event::EVENT_KEYBOARD);
        $this->setSolidness(self::SOLIDNESS_SPECTRAL);
    }

    public function draw(): void
    {
        for($i = 0; $i < count($this->negative); $i++) {
            $this->negative[$i]->draw();
            $this->positive[$i]->draw();
        }
    }

    public function handle(Event $event): bool
    {
        switch($event->getType()) {
            case Event::EVENT_KEYBOARD:
                $this->keyboard($event);
                return true;
            default:
                return parent::handle($event);
        }
    }

    private function keyboard(KeyboardEvent $event): void
    {
        switch ($event->getValue()) {
            case KeyboardEvent::KEY_LOWER_Q:
            case KeyboardEvent::KEY_UPPER_Q:
                GameManager::getInstance()->setGameOver();
                break;
            case KeyboardEvent::KEY_LOWER_T:
            case KeyboardEvent::KEY_UPPER_T:
                $this->transistors[] = new Transistor($this->position->getX() + 7, $this->position->getY() + 3 + 5 * count($this->transistors));
                if (count($this->transistors) === 1) {
                    $this->transistors[0]->setCurrent(true);
                }
                break;
            case KeyboardEvent::KEY_TAB:
                for ($i = 0; $i < count($this->transistors); $i++) {
                    if ($this->transistors[$i]->getCurrent() && !$this->transistors[$i]->hasCurrentPin()) {
                        $this->transistors[$i]->setCurrent();
                        if ($i === count($this->transistors) - 1) {
                            $this->transistors[0]->setCurrent(true);
                        }
                        else {
                            $this->transistors[$i + 1]->setCurrent(true);
                        }
                        break;
                    }
                }
                break;
            default:
                break;
        }
    }
}
