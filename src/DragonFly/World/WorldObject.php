<?php

namespace DragonFly\World;

use DragonFly\Event\Event;
use DragonFly\Manager\GameManager;
use DragonFly\Manager\InputManager;
use DragonFly\Manager\LogManager;
use DragonFly\Manager\ResourceManager;
use DragonFly\Manager\WorldManager;
use DragonFly\Resource\Animation;
use DragonFly\World\Vector;
use Exception;

class WorldObject 
{
    const MAX_ALTITUDE = 5;
    const MAX_EVENTS = 100;
    const OBJECT_VIEW = 'df::ViewObject';
    const OBJECT_WORLD = 'df::WorldObject';
    const SOLIDNESS_HARD = 'hard';
    const SOLIDNESS_SOFT = 'soft';
    const SOLIDNESS_SPECTRAL = 'spectral';
    const SOLIDNESS = [self::SOLIDNESS_HARD, self::SOLIDNESS_SOFT, self::SOLIDNESS_SPECTRAL];

    /**
     * Unique game engine define identifier.
     *
     * @var int
     */
    protected $id;

    /**
     * If false, the object is not acted apon.
     *
     * @var bool
     */
    protected $active;

    /**
     * 0 to MAX supported. Lower are drawn first.
     *
     * @var int
     */
    protected $altitude;

    /**
     * Animation associated with object.
     *
     * @var Animation
     */
    protected $animation;

    /**
     * Box for sprite boundary and collisions.
     *
     * @var Box
     */
    protected $box;

    /**
     * Direction vector.
     *
     * @var Vector
     */
    protected $direction;

    /**
     * Number of events.
     *
     * @var int
     */
    protected $eventCount;

    /**
     * List of events.
     *
     * @var array
     */
    protected $events;

    /**
     * True if not allowed to move onto soft objects.
     *
     * @var bool
     */
    protected $noSoft;

    /**
     * Position in the game world.
     *
     * @var Vector
     */
    protected $position;

    /**
     * Solidness of object.
     *
     * @var string
     */
    protected $solidness;

    /**
     * Object speed in direction.
     *
     * @var float
     */
    protected $speed;

    /**
     * Game programmer defined type.
     *
     * @var string
     */
    protected $type;

    /**
     * If true, object gets drawn.
     *
     * @var bool
     */
    protected $visible;

    /**
     * Single instance of the id generator.
     *
     * @var int
     */
    protected static $objectId = 0;

    /**
     * Instantiate class and properties.
     *
     * @param string $type
     * @param float $x
     * @param float $y
     * @param int $altitude
     * @param bool $solidness
     * @param bool $noSoft
     */
    public function __construct(...$params)
    {   
        $type = isset($params[0]) && is_string($params[0]) && !is_null($params[0]) ? $params[0] : self::OBJECT_WORLD;
        $x = isset($params[1]) && is_numeric($params[1])? $params[1] : 0;
        $y = isset($params[2]) && is_numeric($params[2])? $params[2] : 0;
        $altitude = isset($params[3]) ? $params[3] : 3;
        $solidness = isset($params[4]) && is_string($params[4]) && !is_null($params[0]) ? $params[4] : self::SOLIDNESS_HARD;
        $noSoft = isset($params[5]) && is_bool($params[5]) && $params[5] ?: false;

        $this->altitude = $altitude;
        $this->active = true;
        $this->eventCount = 0;
        $this->events = [];
        $this->solidness = $solidness;
        $this->visible = true;
        
        $this->setAnimation(new Animation);
        $this->setBox(new Box);
        $this->setId(self::$objectId++);
        $this->setNoSoft($noSoft);
        $this->setPosition(new Vector($x, $y));
        $this->setType($type);
        $this->setVisible();
        $this->setVelocity(new Vector);

        try {
            WorldManager::getInstance()->insertObject($this);
        }
        catch (Exception $e) {
            LogManager::getInstance()->error($e);
        }
    }

    /**
     * Destory class and properties.
     */
    public function __destruct()
    {
        try {
            WorldManager::getInstance()->removeObject($this);
            unset($this->active);
            unset($this->altitude);
            unset($this->animation);
            unset($this->direction);
            for($i = $this->eventCount - 1; $i > 0; $i--) {
                $this->unregisterInterest($this->events[$i]);
            }
            unset($this->eventCount);
            unset($this->events);
            unset($this->id);
            unset($this->position);
            unset($this->speed);
            unset($this->solidness);
            unset($this->type);
            unset($this->visible);
        }
        catch (Exception $e) { }
    }

    /**
     * Convert the WorldObject into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $object = "{";
        $object .= "\"id\":{$this->getId()},";
        $object .= sprintf("\"active\":%s", $this->getActive() ? "true," : "false,");
        $object .= "\"altitude\":{$this->getAltitude()},";
        $object .= "\"animation\":{$this->getAnimation()},";
        $object .= "\"box\":{$this->getBox()},";
        $object .= "\"direction\":{$this->getDirection()},";
        $object .= "\"eventCount\":{$this->eventCount},";
        $object .= "\"events\":[";
        for($i = 0; $i < $this->eventCount; $i++) {
            $object .= "\"{$this->events[$i]}\"";
            if ($i !== $this->eventCount - 1) {
                $object .= ",";
            }
        }
        $object .= "],";
        $object .= sprintf("\"noSoft\":%s", $this->getNoSoft() ? "true," : "false,");
        $object .= "\"position\":{$this->getPosition()},";
        $object .= "\"solidness\":\"{$this->getSolidness()}\",";
        $object .= "\"speed\":{$this->getSpeed()},";
        $object .= "\"type\":\"{$this->getType()}\",";
        $object .= sprintf("\"visible\":%s", $this->getVisible() ? "true" : "false");
        $object .= "}";
        return $object;
    }

    /**
     * Draw the world object to the screen via DispalyManager.
     *
     * @return void
     */
    public function draw(): void 
    { 
        try {
            $this->animation->draw($this->position);
        }
        catch (Exception $e) {
            LogManager::getInstance()->error($e);
        }
    }

    /**
     * Return activeness of object. Objects not active are not acted apon by engine.
     *
     * @return boolean
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * Get altitude of an object.
     *
     * @return integer
     */
    public function getAltitude(): int
    {
        return $this->altitude;
    }

    /**
     * Get animation for object.
     *
     * @return Animation
     */
    public function getAnimation(): Animation
    {
        return $this->animation;
    }

    /**
     * Get object bounding box.
     *
     * @return Box
     */
    public function getBox(): Box
    {
        return $this->box;
    }

    /**
     * Get direction of object.
     *
     * @return Vector
     */
    public function getDirection(): Vector
    {
        return $this->direction;
    }

    /**
     * Get unique game engine defined identifier.
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get no soft setting (true cannot move onto SOFT objects).
     *
     * @return boolean
     */
    public function getNoSoft(): bool
    {
        return $this->noSoft;
    }

    /**
     * Get the position in the game world.
     *
     * @return Vector
     */
    public function getPosition(): Vector
    {
        return $this->position;
    }

    /**
     * Get object solidness.
     *
     * @return string
     */
    public function getSolidness(): string
    {
        return $this->solidness;
    }

    /**
     * Get speed of object.
     *
     * @return float
     */
    public function getSpeed(): float
    {
        return $this->speed;
    }
    
    /**
     * Get the game programmer defined type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get velocity of object based on direction and speed.
     *
     * @return Vector
     */
    public function getVelocity(): Vector
    {
        $velocity = new Vector($this->getDirection()->getX(), $this->getDirection()->getY());
        return $velocity->scale($this->speed);
    }

    /**
     * Return visibility of object. Objecets not visible are not drawn.
     *
     * @return boolean
     */
    public function getVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Handle event (default is to ignore everything).
     *
     * @param Event $event
     * @return boolean Return false if ignored, else true if handled.
     */
    public function handle(Event $event): bool
    {
        return false;
    }

    /**
     * True if HARD or SOFT, else false.
     *
     * @return boolean
     */
    public function isSolid(): bool
    {
        return in_array($this->getSolidness(), [self::SOLIDNESS_HARD, self::SOLIDNESS_SOFT]);
    }

    /**
     * Predict object position based on speed and direction.
     *
     * @return Vector Predicted position.
     */
    public function predictPosition(): Vector
    {
        return $this->getVelocity()->add($this->position);
    }

    /**
     * Register for interest in event.
     * Keeps track of manager and event.
     *
     * @param string $eventType
     * @return boolean
     */
    public function registerInterest(string $eventType): bool
    {
        if (!in_array($eventType, $this->events) && $this->eventCount < self::MAX_EVENTS) {
            $this->events[$this->eventCount++] = $eventType;
            switch($eventType) {
                case Event::EVENT_STEP:
                    return GameManager::getInstance()->registerInterest($this, $eventType);
                case Event::EVENT_KEYBOARD:
                    return InputManager::getInstance()->registerInterest($this, $eventType);
                default:
                    return WorldManager::getInstance()->registerInterest($this, $eventType);
            }
        }
        return false;
    }

    /**
     * Set activeness of object. Objects not active are not acted apon by engine.
     *
     * @param boolean $active
     * @return void
     */
    public function setActive(bool $active = true): void
    {
        WorldManager::getInstance()->getObjects()->updateActive($this, $active);
        $this->active = $active;
    }

    /**
     * Set altitude of object. 
     *
     * @param integer $altitude
     * @throws Exception Altitude is out of bounds.
     * @return void
     */
    public function setAltitude(int $altitude): void
    {
        if ($altitude < 0 || $altitude >= self::MAX_ALTITUDE) {
            throw new Exception("Invalid altitude supplied for {$this->getType()}({$this->getId()}): {$altitude}");
        }
        WorldManager::getInstance()->getObjects()->updateAltitude($this, $altitude);
        $this->altitude = $altitude;
    }

    /**
     * Set animation of object to a new one.
     *
     * @param Animation $animation
     * @return void
     */
    public function setAnimation(Animation $animation): void
    {
        $this->animation = $animation;
    }

    /**
     * Set object bounding box.
     *
     * @param Box $box
     * @return void
     */
    public function setBox(Box $box): void
    {
        $this->box = $box;
    }

    /**
     * Set direction of object.
     *
     * @param Vector $direction
     * @return void
     */
    public function setDirection(Vector $direction): void
    {
        $this->direction = $direction;
    }

    /**
     * Set unique game engine defined identifier.
     *
     * @param integer $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Set no soft setting (true cannot move onto SOFT objects).
     *
     * @param boolean $noSoft
     * @return void
     */
    public function setNoSoft(bool $noSoft = false): void
    {
        $this->noSoft = $noSoft;
    }

    /**
     * Set the position in the game world.
     *
     * @param Vector $position
     * @return void
     */
    public function setPosition(Vector $position): void
    {
        $this->position = $position;
    }

    /**
     * Set object solidness.
     *
     * @param string $solidness
     * @throws Exception Value supplied is not in the list of acceptable values.
     * @return void
     */
    public function setSolidness(string $solidness): void
    {
        if (!in_array($solidness, self::SOLIDNESS)) {
            throw new Exception("Invalid solidness supplied for {$this->getType()}({$this->getId()}): \"{$solidness}\"");
        }
        WorldManager::getInstance()->getObjects()->updateSolidness($this, $solidness);
        $this->solidness = $solidness;
    }

    /**
     * Set sprite for the object to animate.
     *
     * @param string $label
     * @return void
     */
    public function setSprite(string $label): void
    {
        $this->animation->setSprite(ResourceManager::getInstance()->getSprite($label));
        if (!is_null($this->animation->getSprite())) {
            $this->box->setHorizontal($this->animation->getSprite()->getWidth());
            $this->box->setVertical($this->animation->getSprite()->getHeight());
        }
        else {
            $this->box->setHorizontal(1);
            $this->box->setVertical(1);
        }
    }

    /**
     * Set speed of object.
     *
     * @param float $speed
     * @return void
     */
    public function setSpeed(float $speed): void
    {
        $this->speed = $speed;
    }

    /**
     * Set the game programmer defined type.
     *
     * @param string $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Set direction and speed of an object.
     *
     * @param Vector $velocity
     * @return void
     */
    public function setVelocity(Vector $velocity): void
    {
        $this->setDirection($velocity);
        $this->setSpeed($velocity->getMagnitude());
        $this->direction->normalize();
    }

    /**
     * Set visibility of object. Objects not visible are not drawn.
     *
     * @param boolean $visible
     * @return void
     */
    public function setVisible(bool $visible = true): void
    {
        WorldManager::getInstance()->getObjects()->updateVisible($this, $visible);
        $this->visible = $visible;
    }

    /**
     * Unregister interest in event.
     *
     * @param string $eventType
     * @return boolean
     */
    public function unregisterInterest(string $eventType): bool
    {
        for($i = 0; $i < $this->eventCount; $i++) {
            if (isset($this->events[$i]) && $this->events[$i] === $eventType) {
                for ($j = $i + 1; $j < $this->eventCount; $j++) {
                    $this->events[$j - 1] = $this->events[$j];
                }
                $this->eventCount--;
                switch ($eventType) {
                    case Event::EVENT_STEP:
                        return GameManager::getInstance()->unregisterInterest($this, $eventType);
                    case Event::EVENT_KEYBOARD:
                        return InputManager::getInstance()->unregisterInterest($this, $eventType);
                    default:
                        return WorldManager::getInstance()->unregisterInterest($this, $eventType);
                }
            }
        }
        return false;
    }
}
