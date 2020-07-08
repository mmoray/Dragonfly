<?php

namespace DragonFly\Event;

class Event
{
    const EVENT_COLLISION = 'df::CollisionEvent';
    const EVENT_KEYBOARD = 'df::KeyboardEvent';
    const EVENT_OUT = 'df::OutEvent';
    const EVENT_STEP = 'df::StepEvent';
    const EVENT_UNDEFINED = 'df::UndefinedEvent';
    const EVENT_VIEW = 'df::ViewEvent';

    /**
     * Event type.
     *
     * @var string
     */
    protected $type;

    /**
     * Instantiate class and properties.
     * 
     * @param string $type
     */
    public function __construct(...$params)
    {
        $this->setType(isset($params[0]) && !is_null($params[0]) ? $params[0] : self::EVENT_UNDEFINED);
    }

    /**
     * Destory class and propereties.
     */
    public function __destruct()
    {
        unset($this->type);
    }

    /**
     * Convert the Event into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $event = "{";
        $event .= "\"type\":{$this->getType()}";
        $event .= "}";
        return $event;
    }

    /**
     * Get event type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set event type.
     *
     * @param string $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
