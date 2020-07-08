<?php

namespace Transistor\Objects;

use DragonFly\Base\Utility;
use DragonFly\Event\Event;
use DragonFly\Event\KeyboardEvent;
use DragonFly\Manager\DisplayManager;
use DragonFly\Manager\LogManager;
use DragonFly\Manager\WorldManager;
use DragonFly\World\Vector;
use DragonFly\World\WorldObject;

class Pin extends WorldObject
{
    const CHAR_PIN = 'P';
    const OBJECT_PIN = 'pin';
    const SPRITE_PATH = __DIR__ . '/../Sprites/Pin.txt';

    protected $b_negative;
    protected $b_positive;
    protected $connections;
    protected $current;
    protected $display;
    protected $wires;

    public function __construct(...$params)
    {
        $x = isset($params[0]) && is_numeric($params[0]) ? $params[0] : 0;
        $y = isset($params[1]) && is_numeric($params[1]) ? $params[1] : 0;
        $display = isset($params[2]) && is_string($params[2]) ? $params[2] : self::CHAR_PIN;
        parent::__construct(self::OBJECT_PIN, $x, $y, self::MAX_ALTITUDE - 1);
        $this->setSprite(self::OBJECT_PIN);

        $this->connections = [];
        $this->wires = ['connections' => [], 'negative' => [], 'positive' => []];
        $this->registerInterest(Event::EVENT_KEYBOARD);
        $this->setBNegative();
        $this->setBPositive();
        $this->setCurrent();
        $this->setDisplay($display);
        $this->setSolidness(self::SOLIDNESS_SPECTRAL);
        LogManager::getInstance()->info("b_negative: " . is_bool($this->b_negative));
    }

    public function addConnection(Pin $pin): void
    {
        if (!in_array($pin, $this->connections, true)) {
            $this->connections[] = $pin;
        }
    }

    public function draw(): void
    {
        $this->animation->getSprite()->getFrame(0)->setFrame($this->display);
        parent::draw();
        $this->animation->getSprite()->getFrame(0)->setFrame(self::CHAR_PIN);
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

    public function removeConnection(Pin $pin): void
    {
        if (($key = array_search($pin, $this->connections, true)) !== false) {
            unset($this->connections[$key]);
        }
    }

    public function getBNegative(): bool
    {
        return $this->b_negative;
    }

    public function getBPositive(): bool
    {
        return $this->b_positive;
    }

    public function getCurrent(): bool
    {
        return $this->current;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    public function setBNegative(bool $bNegative = false): void
    {
        $this->b_negative = $bNegative;
        if ($this->b_negative) {
            for ($i = WorldManager::getInstance()->getView()->getCorner()->getX() + 2; $i < $this->getPosition()->getX(); $i++) {
                $this->wires['negative'][] = new Wire($i, $this->getPosition()->getY(), false, true);
            }
        }
        else {
            foreach ($this->wires['negative'] AS $index => $wire) {
                unset($this->wires['negative'][$index]);
                WorldManager::getInstance()->markForDelete($wire);
            }
        }
    }

    public function setBPositive(bool $bPositive = false): void
    {
        $this->b_positive = $bPositive;
        if ($this->b_positive) {
            for ($i = WorldManager::getInstance()->getView()->getCorner()->getX() + 3; $i < $this->getPosition()->getX(); $i++) {
                $this->wires['positive'][] = new Wire($i, $this->getPosition()->getY(), true, true);
            }
        }
        else {
            foreach ($this->wires['positive'] AS $index => $wire) {
                unset($this->wires['positive'][$index]);
                WorldManager::getInstance()->markForDelete($wire);
            }
        }
    }

    public function setCurrent(bool $current = false)
    {
        $this->current = $current;
        if ($this->current) {
            $this->animation->setSlowdownCount(0);
        }
        else {
            $this->animation->setIndex(0);
            $this->animation->setSlowdownCount(-1);
        }
    }

    public function setDisplay(string $display): void
    {
        $this->display = $display;
    }

    private function keyboard(KeyboardEvent $event): void
    {
        if ($this->getCurrent()) {
            switch($event->getValue()) {
                case KeyboardEvent::KEY_LOWER_N:
                case KeyboardEvent::KEY_UPPER_N:
                    $this->setBNegative(!$this->getBNegative());
                    break;
                case KeyboardEvent::KEY_LOWER_P:
                case KeyboardEvent::KEY_UPPER_P:
                    $this->setBPositive(!$this->getBPositive());
                    break;
                default:
                    break;
            }
        }
    }
}
