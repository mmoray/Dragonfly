<?php

namespace DragonFly\Resource;

use DragonFly\Manager\DisplayManager;
use DragonFly\Manager\LogManager;
use DragonFly\World\Color;
use DragonFly\World\Vector;
use Exception;

class Frame
{
    /**
     * All frame characters stored as string.
     *
     * @var string
     */
    private $frame;

    /**
     * Height of frame.
     *
     * @var int
     */
    private $height;

    /**
     * Width of frame.
     *
     * @var int
     */
    private $width;

    /**
     * Instantiate class and properties.
     *
     * @param integer $width
     * @param integer $height
     * @param string $frame
     */
    public function __construct(int $width = 0, int $height = 0, string $frame = null)
    {
        $this->setFrame($frame);
        $this->setHeight($height);
        $this->setWidth($width);
    }

    /**
     * Convert the Frame into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $frame = "{";
        $frame .= "\"frame\": \"{$this->getFrame()} \",";
        $frame .= "\"height\": {$this->getHeight()},";
        $frame .= "\"width\": {$this->getWidth()}";
        $frame .= "}";
        return $frame;
    }
    
    /**
     * Draw self, center position (x, y) with color. 
     * Note: Top left coordinate is (0, 0).
     *
     * @param Vector $position
     * @param Color $color
     * @return boolean
     */
    public function draw(Vector $position, Color $color = null, string $transparent = null): bool
    {
        try {
            if (!is_null($this->frame)) {
                $chars = str_split($this->frame);
                $xOffset = $this->getWidth() / 2;
                $yOffset = $this->getHeight() / 2;
                for ($y = 0; $y < $this->getHeight(); $y++) {
                    for ($x = 0; $x < $this->getWidth(); $x++) {
                        if (is_null($transparent) || $chars[$y * $this->getWidth() + $x] !== $transparent) {
                            $tempPosition = new Vector($position->getX() + $x - $xOffset, $position->getY() + $y - $yOffset);
                            DisplayManager::getInstance()->drawChar($tempPosition, $chars[$y * $this->width + $x], $color);
                        }
                    }
                }
                return true;
            }
        }
        catch (Excpetion $e) {
            LogManager::getInstance()->error($e->getMessage());
        }
        return false;
    }

    /**
     * Get frame characters (stored as a string).
     *
     * @return string
     */
    public function getFrame(): string
    {
        return $this->frame;
    }

    /**
     * Get height of frame.
     *
     * @return integer
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Get width of frame.
     *
     * @return integer
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Set frame characters (stored as string).
     *
     * @param string $frame
     * @return void
     */
    public function setFrame(string $frame = null): void
    {
        $this->frame = $frame;
    }

    /**
     * Set frame height.
     *
     * @param integer $height
     * @return void
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    /**
     * Set frame width.
     *
     * @param integer $width
     * @return void
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }
}
