<?php

namespace DragonFly\Resource;

use DragonFly\Manager\DisplayManager;
use DragonFly\Manager\LogManager;
use DragonFly\World\Color;
use DragonFly\World\Vector;
use Exception;

class Sprite
{
    /**
     * Optional color for entire sprite.
     *
     * @var Color
     */
    private $color;

    /**
     * Acutal number of frames sprite has.
     *
     * @var int
     */
    private $frameCount;

    /**
     * Array of frames.
     *
     * @var array
     */
    private $frames;
    
    /**
     * Sprite height.
     *
     * @var int
     */
    private $height;

    /**
     * Text label to identify sprite.
     *
     * @var string
     */
    private $label;

    /**
     * Max number of frames sprite can have.
     *
     * @var int
     */
    private $maxFrameCount;

    /**
     * Animation slowdown (1 = no slowdown, 0 = stop).
     *
     * @var int
     */
    private $slowdown;

    /**
     * Set the tranparent string for the sprite.
     *
     * @var string
     */
    private $transparency;

    /**
     * Sprite width.
     *
     * @var int
     */
    private $width;

    /**
     * Instantiate class and properties.
     *
     * @param integer $maxFrameCount
     */
    public function __construct(int $maxFrameCount)
    {
        $this->maxFrameCount = $maxFrameCount;
        $this->frameCount = 0;
        $this->frames = [];
        $this->setColor(new Color(DisplayManager::WINDOW_BACKGROUND_COLOR_DEFAULT, DisplayManager::WINDOW_FOREGROUND_COLOR_DEFAULT));
        $this->setHeight(0);
        $this->setSlowdown(1);
        $this->setTransparency();
        $this->setWidth(0);
    }

    /**
     * Destory class and properties.
     */
    public function __destruct()
    {
        unset($this->color);
        for ($i = $this->frameCount - 1; $i >= 0; $i--) {
            unset($this->frames[$i]);
        }
        unset($this->frameCount);
        unset($this->frames);
        unset($this->height);
        unset($this->label);
        unset($this->maxFrameCount);
        unset($this->slowdown);
        unset($this->transparency);
        unset($this->width);
    }

    /**
     * Convert the Sprite into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $sprite = "{";
        $sprite .= "\"label\":\"{$this->getLabel()}\",";
        $sprite .= "\"color\":{$this->getColor()},";
        $sprite .= "\"frameCount\":{$this->getframeCount()},";
        $sprite .= "\"frames\":[";
        for($i = 0; $i < $this->getFrameCount(); $i++) {
            $sprite .= "{$this->getFrame($i)}";
            if ($i !== $this->getFrameCount() - 1) {
                $sprite .= ",";
            }
        }
        $sprite .= "],";
        $sprite .= "\"height\":{$this->getHeight()},";
        $sprite .= "\"maxFrameCount\":{$this->maxFrameCount},";
        $sprite .= "\"slowdown\":{$this->getSlowdown()},";
        $sprite .= "\"transparency\":" . (is_null($this->getTransparency()) ? "\"\"," : "\"{$this->getTransparency()}\",");
        $sprite .= "\"width\":{$this->getWidth()}";
        $sprite .= "}";
        return $sprite;
    }

    /**
     * Add frame to sprite.
     *
     * @param Frame $frame
     * @return void
     */
    public function addFrame(Frame $frame): void
    {
        if ($this->frameCount === $this->maxFrameCount) {
            throw new Exception("Maximum amount of frames reached for sprite: {$this->getLabel()}");
        }
        $this->frames[$this->frameCount++] = $frame;
    }

    /**
     * Draw indicated frame centered at position (x, y).
     * Note: Top left coordiate is (0, 0).
     *
     * @param integer $index
     * @param Vector $position
     * @return boolean
     */
    public function draw(int $index, Vector $position): bool
    {
        try {
            return $this->getFrame($index)->draw($position, $this->color, $this->transparency);
        }
        catch (Exception $e) {
            LogManager::getInstance()->error($e);
        }
        return false;
    }

    /**
     * Get sprite color.
     *
     * @return Color
     */
    public function getColor(): Color
    {
        return $this->color;
    }

    /**
     * Get sprite frame indicated by index.
     * Return empty frame if out of range.
     *
     * @param integer $index
     * @return Frame
     * @throws Exception Index is out of range.
     */
    public function getFrame(int $index): ?Frame
    {
        if ($index < 0 || $index >= $this->frameCount) {
            throw new Exception("Invalid frame index supllied for sprite: {$this->getLabel()}");
        }
        return $this->frames[$index];
    }

    /**
     * Get total count of frames in sprite.
     *
     * @return integer
     */
    public function getFrameCount(): int
    {
        return $this->frameCount;
    }

    /**
     * Get sprite height.
     *
     * @return integer
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Get sprite label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get animation slowdown value. 
     * Value in multiples of GameManager frame time.
     *
     * @return integer
     */
    public function getSlowdown(): int
    {
        return $this->slowdown;
    }

    /**
     * Get transparency string for the sprite.
     *
     * @return string
     */
    public function getTransparency(): ?string
    {
        return $this->transparency;
    }

    /**
     * Get sprite width.
     *
     * @return integer
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Set sprite color.
     *
     * @param Color $color
     * @return void
     */
    public function setColor(Color $color): void
    {
        $this->color = $color;
    }

    /**
     * Set sprite height.
     *
     * @param integer $height
     * @return void
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    /**
     * Set sprite label.
     *
     * @param string $label
     * @return void
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * Set animation slowdown value.
     * Value in multiples of GameManager fame time.
     *
     * @param integer $slowdown
     * @return void
     */
    public function setSlowdown(int $slowdown): void
    {
        $this->slowdown = $slowdown;
    }
    
    /**
     * Set the transparency string of the sprite.
     *
     * @param string $transparency
     * @return void
     */
    public function setTransparency(string $transparency = null): void
    {
        $this->transparency = $transparency;
    }

    /**
     * Set sprite width.
     *
     * @param integer $width
     * @return void
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }
}
