<?php

namespace DragonFly\Event;

class StepEvent extends Event
{
    /**
     * Iteration number of game loop.
     *
     * @var int
     */
    private $stepCount;

    /**
     * Instantiate class and properties.
     *
     * @param integer $stepCount
     */
    public function __construct(...$params)
    {
        $this->stepCount = isset($params[0]) && is_int($params[0]) ? $params[0] : 0;
        parent::__construct(Event::EVENT_STEP);
    }

    /**
     * Destroy class and properties.
     */
    public function __destruct()
    {
        unset($this->stepCount);
        parent::__destruct();
    }

    /**
     * Convert the StepEvent into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $step = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $step .= "\"stepCount\":{$this->getStepCount()}";
        $step .= "}";
        return $step;
    }

    /**
     * Get step count.
     *
     * @return integer
     */
    public function getStepCount(): int
    {
        return $this->stepCount;
    }

    /**
     * Set step count.
     *
     * @param integer $stepCount
     * @return void
     */
    public function setStepCount(int $stepCount): void
    {
        $this->stepCount = $stepCount;
    }
}
