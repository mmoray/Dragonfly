<?php

namespace DragonFly\World;

use DragonFly\Base\Utility;
use DragonFly\Event\Event;
use DragonFly\Manager\DisplayManager;
use DragonFly\Manager\LogManager;
use DragonFly\Manager\WorldManager;
use DragonFly\Resource\Frame;
use Exception;

class ViewObject extends WorldObject
{
    const LOCATION_TOP_RIGHT = 'tr';
    const LOCATION_TOP_CENTER = 'tc';
    const LOCATION_TOP_LEFT = 'tl';
    const LOCATION_CENTER_RIGHT = 'cr';
    const LOCATION_CENTER_CENTER = 'cc';
    const LOCATION_CENTER_LEFT = 'cl';
    const LOCATION_BOTTOM_RIGHT = 'br';
    const LOCATION_BOTTOM_CENTER = 'bc';
    const LOCATION_BOTTOM_LEFT = 'bl';
    const LOCATIONS = [self::LOCATION_TOP_LEFT, self::LOCATION_TOP_CENTER, self::LOCATION_TOP_RIGHT, self::LOCATION_CENTER_LEFT, self::LOCATION_CENTER_CENTER, self::LOCATION_CENTER_RIGHT, self::LOCATION_BOTTOM_LEFT, self::LOCATION_BOTTOM_CENTER, self::LOCATION_BOTTOM_RIGHT];

    /**
     * True if should draw value.
     *
     * @var bool
     */
    protected $drawValue;

    /**
     * True if border should be drawn around display.
     *
     * @var bool
     */
    protected $border;

    /**
     * Frame used for drawing.
     *
     * @var Frame
     */
    protected $frame;

    /**
     * Display location.
     *
     * @var string
     */
    protected $location;

    /**
     * Display vlaue.
     *
     * @var int
     */
    protected $value;

    /**
     * Label for value.
     *
     * @var string
     */
    protected $viewString;

    /**
     * Instantiate construct and properties.
     */
    public function __construct(...$params)
    {
        parent::__construct();
        $this->setAltitude(self::MAX_ALTITUDE - 1);
        $this->setSolidness(self::SOLIDNESS_SPECTRAL);
        $this->setType(self::OBJECT_VIEW);

        $this->frame = new Frame;
        $this->setLocation(self::LOCATION_TOP_CENTER);
        $this->setBorder(true);
        $this->setColor(new Color(DisplayManager::WINDOW_BACKGROUND_COLOR_DEFAULT, DisplayManager::WINDOW_FOREGROUND_COLOR_DEFAULT));
        $this->setDrawValue();
        $this->setValue(0);

        $this->registerInterest(Event::EVENT_VIEW);
    }

    /**
     * Convert the ViewObject into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $view = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $view .= sprintf("\"border\":%s", $this->getBorder() ? "true," : "false,"); 
        $view .= "\"color\":{$this->getColor()},";
        $view .= sprintf("\"drawValue\":%s", $this->getDrawValue() ? "true," : "false,"); 
        $view .= "\"frame\":{$this->frame},";
        $view .= "\"location\":\"{$this->getLocation()}\",";
        $view .= "\"frame\":{$this->getValue()}";
        $view .= "}";
        return $view;
    }

    /**
     * Draw view string and value.
     *
     * @return void
     */
    public function draw(): void
    {
        $content = $this->viewString;
        if ($this->drawValue) {
            $content .= " {$this->value}";
        }
        $height = $width = 1;
        if ($this->border) {
            $border = sprintf('+%s+', str_repeat('-', strlen($content)));
            $content = "{$border}|{$content}|{$border}";
            $height = 3;
            $width = strlen($border);
        }
        else {
            $width = strlen($content);
        }
        $this->frame->setFrame($content);
        $this->frame->setHeight($height);
        $this->frame->setWidth($width);
        if (in_array($this->location, [self::LOCATION_TOP_CENTER, self::LOCATION_TOP_LEFT, self::LOCATION_TOP_RIGHT])) {
            $this->frame->draw($this->position->add(new Vector(0, $height / 2)), $this->color);
        }
        else if (in_array($this->location, [self::LOCATION_BOTTOM_CENTER, self::LOCATION_BOTTOM_LEFT, self::LOCATION_BOTTOM_RIGHT])) {
            $this->frame->draw($this->position->subtract(new Vector(0, $height / 2)), $this->color);
        }
        else {
            $this->frame->draw($this->position, $this->color);
        }
    }

    /**
     * Get view border (true = display border).
     *
     * @return boolean
     */
    public function getBorder(): bool
    {
        return $this->border;
    }

    /**
     * Get view color.
     *
     * @return Color
     */
    public function getColor(): Color
    {
        return $this->color;
    }

    /**
     * Get draw value (true if draw value with dispaly string).
     *
     * @return boolean
     */
    public function getDrawValue(): bool
    {
        return $this->drawValue;
    }

    /**
     * Get general location of View Object on screen.
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Get view value.
     *
     * @return integer
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Get view display string.
     *
     * @return string
     */
    public function getViewString(): string
    {
        return $this->viewString;
    }

    /**
     * Handle event (default is to ignore everything).
     *
     * @param Event $event
     * @return boolean Return false if ignored, else true if handled.
     */
    public function handle(Event $event): bool
    {
        if ($event->getType() === Event::EVENT_VIEW && $event->getTag() === $this->viewString) {
            LogManager::getInstance()->info("{$event}");
            if ($event->getDelta()) {
                $this->value += $event->getValue();
            }
            else {
                $this->value = $event->getValue();
            }
            return true;
        }
        return parent::handle($event);
    }

    /**
     * Set view border (true = display border).
     *
     * @param boolean $border
     * @return void
     */
    public function setBorder(bool $border): void
    {
        if ($border !== $this->border) {
            $this->border = $border;
            $this->setLocation($this->getLocation());
        }
    }

    /**
     * Set view color.
     *
     * @param Color $color
     * @return void
     */
    public function setColor(Color $color): void
    {
        $this->color = $color;
    }

    /**
     * Set true to draw value with display string.
     *
     * @param boolean $drawValue
     * @return void
     */
    public function setDrawValue(bool $drawValue = true): void
    {
        $this->drawValue = $drawValue;
    }

    /**
     * Set general location of ViewObject on screen.
     *
     * @param string $location
     * @return void
     * @throws Exception Invalid location has been supplied.
     */
    public function setLocation(string $location): void
    {
        if (!in_array($location, self::LOCATIONS)) {
            throw new Exception("Invalid location specified for ViewObject: {$location}");
        }
        $worldManager = WorldManager::getInstance();
        $yDelta = 0;
        switch ($location) {
            case self::LOCATION_TOP_LEFT:
                $this->position->setXY($worldManager->getView()->getHorizontal() / 6, 1);
                if (!$this->border) {
                    $yDelta = -1;
                }
                break;
            case self::LOCATION_TOP_CENTER:
                $this->position->setXY($worldManager->getView()->getHorizontal() / 2, 1);
                if (!$this->border) {
                    $yDelta = -1;
                }
                break;
            case self::LOCATION_TOP_RIGHT:
                $this->position->setXY($worldManager->getView()->getHorizontal() * 5 / 6, 1);
                if (!$this->border) {
                    $yDelta = -1;
                }
                break;
            case self::LOCATION_CENTER_LEFT:
                $this->position->setXY($worldManager->getView()->getHorizontal() / 6, $worldManager->getView()->getVertical() / 2);
                break;
            case self::LOCATION_CENTER_CENTER:
                $this->position->setXY($worldManager->getView()->getHorizontal() / 2, $worldManager->getView()->getVertical() / 2);
                break;
            case self::LOCATION_CENTER_RIGHT:
                $this->position->setXY($worldManager->getView()->getHorizontal() * 5 / 6, $worldManager->getView()->getVertical() / 2);
                break;
            case self::LOCATION_BOTTOM_LEFT:
                $this->position->setXY($worldManager->getView()->getHorizontal() / 6, $worldManager->getView()->getVertical() - 1);
                if (!$this->border) {
                    $yDelta = 1;
                }
                break;
            case self::LOCATION_BOTTOM_CENTER:
                $this->position->setXY($worldManager->getView()->getHorizontal() / 2, $worldManager->getView()->getVertical() - 1);
                if (!$this->border) {
                    $yDelta = 1;
                }
                break;
            case self::LOCATION_BOTTOM_RIGHT:
                $this->position->setXY($worldManager->getView()->getHorizontal() * 5 / 6, $worldManager->getView()->getVertical() - 1);
                if (!$this->border) {
                    $yDelta = 1;
                }
                break;
            default:
                break;
        }
        $this->position->setY($this->position->getY() + $yDelta);
        $this->location = $location;
    }

    /**
     * Set view value.
     *
     * @param integer $value
     * @return void
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    /**
     * Set view display string.
     *
     * @param string $viewString
     * @return void
     */
    public function setViewString(string $viewString): void
    {
        $this->viewString = $viewString;
    }
}
