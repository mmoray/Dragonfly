<?php

namespace Transistor\Objects;

use DragonFly\Base\Utility;
use DragonFly\Event\Event;
use DragonFly\Event\KeyboardEvent;
use DragonFly\Manager\DisplayManager;
use DragonFly\World\Color;
use DragonFly\World\Vector;
use DragonFly\World\WorldObject;

class Transistor extends WorldObject
{
    const OBJECT_TANSISTOR = 'transistor';
    const SPRITE_PATH = __DIR__ . '/../Sprites/Transistor.txt';

    private $base;
    private $collector;
    private $current;
    private $emitter;

    public function __construct(...$params)
    {
        $x = isset($params[0]) && is_numeric($params[0])? $params[0] : 0;
        $y = isset($params[1]) && is_numeric($params[1])? $params[1] : 0;
        parent::__construct(self::OBJECT_TANSISTOR, $x, $y, self::MAX_ALTITUDE - 1);
        $this->setSprite(self::OBJECT_TANSISTOR);

        $this->collector = new Collector($this->position->getX(), $this->position->getY() - 1, 'C');
        $this->base = new Base($this->position->getX(), $this->position->getY(), 'B');
        $this->emitter = new Pin($this->position->getX(), $this->position->getY() + 1, 'E');
        $this->animation->getSprite()->setTransparency(' ');
        $this->registerInterest(Event::EVENT_KEYBOARD);
        $this->setCurrent();
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

    public function getCurrent(): bool
    {
        return $this->current;
    }

    public function hasCurrentPin(): bool
    {
        return $this->base->getCurrent() || $this->collector->getCurrent() || $this->emitter->getCurrent();
    }

    public function reset(): void
    {
        $this->base->setCurrent();
        $this->collector->setCurrent();
        $this->emitter->setCurrent();
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

    private function keyboard(KeyboardEvent $event): void
    {
        if ($this->getCurrent()) {
            switch($event->getValue()) {
                case KeyboardEvent::KEY_LOWER_B:
                case KeyboardEvent::KEY_UPPER_B:
                    $this->setBase();
                    break;
                case KeyboardEvent::KEY_LOWER_C:
                case KeyboardEvent::KEY_UPPER_C:
                    $this->setCollector();
                    break;
                case KeyboardEvent::KEY_LOWER_E:
                case KeyboardEvent::KEY_UPPER_E:
                    $this->setEmitter();
                    break;
                case KeyboardEvent::KEY_ESCAPE:
                    $this->reset();
                    break;
                default: 
                    break;
            }
        }
    }

    private function setBase(): void
    {
        if ($this->base->getCurrent()) {
            $this->base->setCurrent();
        }
        else {
            $this->base->setCurrent(true);
            $this->collector->setCurrent();
            $this->emitter->setCurrent();
        }
    }

    private function setCollector(): void
    {
        $this->base->setCurrent();
        $this->collector->setCurrent(true);
        $this->emitter->setCurrent();
    }

    private function setEmitter(): void
    {
        $this->base->setCurrent();
        $this->collector->setCurrent();
        $this->emitter->setCurrent(true);
    }
}
