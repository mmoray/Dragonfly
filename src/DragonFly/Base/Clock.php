<?php

namespace DragonFly\Base;

class Clock
{
    /**
     * Previous time delta() called (microseconds).
     *
     * @var float
     */
    private $prevoiusTime;

    /**
     * Instantiate class and properties.
     */
    public function __construct()
    {
        $this->prevoiusTime = microtime(true);
    }

    /**
     * Resets prevoius time. 
     *
     * @return float Time elapsed (microseconds) since delta() was last called, -1 if error.
     */
    public function delta(): float
    {
        $splitTime = $this->split();
        $this->prevoiusTime = microtime(true);
        return $splitTime;
    }

    /**
     * Does not reset previous time.
     *
     * @return float Time elapsed (microseconds) since delta() was last called, -1 if error.
     */
    public function split(): float
    {
        return microtime(true) - $this->prevoiusTime;
    }
}
