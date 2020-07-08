<?php

namespace Transistor\Objects;

use DragonFly\Base\Utility;
use DragonFly\Manager\DisplayManager;
use DragonFly\Manager\WorldManager;
use DragonFly\World\Color;
use DragonFly\World\WorldObject;
use DragonFly\World\WorldObjectListIterator;

class Wire extends WorldObject
{
    const CHAR_HORIZONTAL = '-';
    const CHAR_VERTICAL = '|';
    const COLOR_BACKGROUND = Color::BLACK;
    const COLOR_OFF = Color::LIGHT_CYAN;
    const COLOR_ON = Color::LIGHT_RED;
    const OBJECT_WIRE = 'wire';

    private $horizontal;
    private $off;
    private $on;
    private $power;

    public function __construct(...$params)
    {
        $x = isset($params[0]) && is_numeric($params[0]) ? $params[0] : 0;
        $y = isset($params[1]) && is_numeric($params[1]) ? $params[1] : 0;
        $power = isset($params[2]) && is_bool($params[2]) ? $params[2] : false;
        $horizontal = isset($params[3]) && is_bool($params[3]) ? $params[3] : false;
        parent::__construct(self::OBJECT_WIRE, $x, $y);
        
        $this->off = new Color(Color::BACKGROUND[self::COLOR_BACKGROUND], Color::FOREGROUND[self::COLOR_OFF]);
        $this->on = new Color(Color::BACKGROUND[self::COLOR_BACKGROUND], Color::FOREGROUND[self::COLOR_ON]);
        $this->setHorizontal($horizontal);
        $this->setPower($power);
        $this->setSolidness(self::SOLIDNESS_SOFT);
    }

    public function draw(): void
    {
        $worldPosition = Utility::getWorldBox($this);
        DisplayManager::getInstance()->drawChar($worldPosition->getCorner(), $this->horizontal ? self::CHAR_HORIZONTAL : self::CHAR_VERTICAL, $this->power ? $this->on : $this->off);
    }

    public function getHorizontal(): bool
    {
        return $this->horizontal;
    }

    public function getPower(): bool
    {
        return $this->power;
    }

    public function setHorizontal(bool $horizontal): void
    {
        $this->horizontal = $horizontal;
    }

    public function setPower(bool $power): void
    {
        $this->power = $power;
    }
}
