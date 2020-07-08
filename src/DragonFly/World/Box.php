<?php

namespace DragonFly\World;

class Box
{
    /**
     * Upper left corner.
     *
     * @var Vector
     */
    private $corner;

    /**
     * Horizontal dimension.
     *
     * @var float
     */
    private $horizontal;
    
    /**
     * Vertical dimension.
     *
     * @var float
     */
    private $vertical;

    /**
     * Instantiate class and properties.
     *
     * @param Vector $corner
     * @param float $horizontal
     * @param float $vertical
     */
    public function __construct(Vector $corner = null, float $horizontal = 1, float $vertical = 1)
    {
        $this->corner = is_null($corner) ? new Vector : $corner;
        $this->horizontal = $horizontal;
        $this->vertical = $vertical;
    }

    /**
     * Destroy class and properties.
     */
    public function __destruct()
    {
        unset($this->corner);
        unset($this->horizontal);
        unset($this->vertical);
    }

    /**
     * Convert the box into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $box = "{";
        $box .= "\"corner\":{$this->getCorner()},";
        $box .= "\"horizontal\":{$this->getHorizontal()},";
        $box .= "\"vertical\":{$this->getVertical()}";
        $box .= "}";
        return $box;
    }

    /**
     * Get upper left corner of box.
     *
     * @return Vector
     */
    public function getCorner(): Vector
    {
        return $this->corner;
    }

    /**
     * Get horizontal dimension of box.
     *
     * @return float
     */
    public function getHorizontal(): float
    {
        return $this->horizontal;
    }

    /**
     * Get vertical dimension of box.
     *
     * @return float
     */
    public function getVertical(): float
    {
        return $this->vertical;
    }

    /**
     * Set upper left corner of box.
     *
     * @param Vector $corner
     * @return void
     */
    public function setCorner(Vector $corner): void
    {
        $this->corner = $corner;
    }

    /**
     * Set horizontal dimension of box.
     *
     * @param float $horizontal
     * @return void
     */
    public function setHorizontal(float $horizontal): void
    {
        $this->horizontal = $horizontal;
    }
    
    /**
     * Set vertical dimension of box.
     *
     * @param float $vertical
     * @return void
     */
    public function setVertical(float $vertical): void
    {
        $this->vertical = $vertical;
    }
}
