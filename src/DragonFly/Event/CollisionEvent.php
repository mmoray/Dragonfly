<?php

namespace DragonFly\Event;

use DragonFly\World\Vector;
use DragonFly\World\WorldObject;

class CollisionEvent extends Event
{
    /**
     * Where collision occurred.
     *
     * @var Vector
     */
    private $position;

    /**
     * Object moving, causing collision.
     *
     * @var WorldObject
     */
    private $offending;

    /**
     * Object being collided with.
     *
     * @var WorldObject
     */
    private $offended;

    /**
     * Instantiate class and properties.
     * 
     * @param WorldObject $offending
     * @param WorldObject $offended
     * @param Vector $position
     */
    public function __construct(...$params)
    {
        $this->setOffending(isset($params[0]) ? $params[0] : null);
        $this->setOffended(isset($params[1]) ? $params[1] : null);
        $this->setPosition(isset($params[2]) ? $params[2] : null);
        parent::__construct(self::EVENT_COLLISION);
    }

    /**
     * Convert the CollisionEvent into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $collision = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $collision .= "\"position\":{$this->getPosition()},";
        $collision .= "\"offended\":{$this->getOffended()},";
        $collision .= "\"offending\":{$this->getOffending()}";
        $collision .= "}";
        return $collision;
    }

    /**
     * Get offended object.
     *
     * @return WorldObject|null
     */
    public function getOffended(): ?WorldObject
    {
        return $this->offended;
    }

    /**
     * Get offending object.
     *
     * @return WorldObject|null
     */
    public function getOffending(): ?WorldObject
    {
        return $this->offending;
    }

    /**
     * Get position of collision.
     *
     * @return Vector|null
     */
    public function getPosition(): ?Vector
    {
        return $this->position;
    }

    /**
     * Set offended object.
     *
     * @param WorldObject $offended
     * @return void
     */
    public function setOffended(WorldObject $offended = null): void
    {
        $this->offended = $offended;
    }

    /**
     * Set offending object.
     *
     * @param WorldObject $offending
     * @return void
     */
    public function setOffending(WorldObject $offending = null): void
    {
        $this->offending = $offending;
    }

    /**
     * Set position of collision.
     *
     * @param Vector $position
     * @return void
     */
    public function setPosition(Vector $position = null): void
    {
        $this->position = $position;
    }
}
