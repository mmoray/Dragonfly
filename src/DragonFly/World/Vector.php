<?php

namespace DragonFly\World;

class Vector
{
    /**
     * Horizontal component.
     *
     * @var float
     */
    private $x;

    /**
     * Vertical component.
     *
     * @var float
     */
    private $y;

    /**
     * Instantiate class and properties.
     *
     * @param float $x
     * @param float $y
     */
    public function __construct(float $x = 0.0, float $y = 0.0)
    {
        $this->setX($x);
        $this->setY($y);
    }

    /**
     * Destory class and properties.
     */
    public function __destruct()
    {
        unset($this->x);
        unset($this->y);
    }

    /**
     * Convert the Vector into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $vector = "{";
        $vector .= "\"x\":{$this->getX()},";
        $vector .= "\"y\":{$this->getY()}";
        $vector .= "}";
        return $vector;
    }

    /**
     * Add this vector to the specified vector.
     *
     * @param Vector $vector
     * @return Vector
     */
    public function add(Vector $vector): Vector
    {
        return new Vector($this->getX() + $vector->getX(), $this->getY() + $vector->getY());
    }

    /**
     * Divide this vector by the specified vector.
     *
     * @param Vector $vector
     * @return Vector
     */
    public function divide(Vector $vector): Vector
    {
        return new Vector($this->getX() / $vector->getX(), $this->getY() / $vector->getY());
    }

    /**
     * Determine if this vector is equal to the specified vector.
     *
     * @param Vector $vector
     * @return boolean
     */
    public function equal(Vector $vector): bool
    {
        return $this->getX() === $vector->getX() && $this->getY() === $vector->getY();
    }

    /**
     * Determine the magnitude of the vector.
     *
     * @return float
     */
    public function getMagnitude(): float
    {
        return sqrt(($this->getX() * $this->getX()) + ($this->getY() * $this->getY()));
    }

    /**
     * Get horizontal component.
     *
     * @return float
     */
    public function getX(): float
    {
        return $this->x;
    }

    /**
     * Get vertical component.
     *
     * @return float
     */
    public function getY(): float
    {
        return $this->y;
    }

    /**
     * Multiply this vector with the specified vector.
     *
     * @param Vector $vector
     * @return Vector
     */
    public function multiply(Vector $vector): Vector
    {
        return new Vector($this->getX() * $vector->getX(), $this->getY() * $vector->getY());
    }

    /**
     * Normalize vector.
     *
     * @return Vector
     */
    public function normalize(): Vector
    {
        $length = $this->getMagnitude();
        if ($length > 0) {
            $this->setXY($this->getX() / $length, $this->getY() / $length);
        }
        return $this;
    }

    /**
     * Return the negated version of this vector.
     *
     * @return Vector
     */
    public function not(): Vector
    {
        return new Vector($this->getX() * -1, $this->getY() * -1);
    }

    /**
     * Determine if this vector is not equal to the specified vector.
     *
     * @param Vector $vector
     * @return boolean
     */
    public function notEqual(Vector $vector): bool
    {
        return !$this->equal($vector);
    }

    /**
     * Scale vector.
     *
     * @param float $scale
     * @return Vector
     */
    public function scale(float $scale): Vector
    {
        $this->setX($this->getX() * $scale);
        $this->setY($this->getY() * $scale);
        return $this;
    }

    /**
     * Set horizontal component.
     *
     * @param float $x
     * @return void
     */
    public function setX(float $x): void
    {
        $this->x = $x;
    }

    /**
     * Set horizontal and vertical components.
     *
     * @param float $x
     * @param float $y
     * @return void
     */
    public function setXY(float $x, float $y): void
    {
        $this->setX($x);
        $this->setY($y);
    }

    /**
     * Set vertical component.
     *
     * @param float $y
     * @return void
     */
    public function setY(float $y): void
    {
        $this->y = $y;
    }

    /**
     * Subtract this vector by the specified vector.
     *
     * @param Vector $vector
     * @return Vector
     */
    public function subtract(Vector $vector): Vector
    {
        return new Vector($this->getX() - $vector->getX(), $this->getY() - $vector->getY());
    }
}
