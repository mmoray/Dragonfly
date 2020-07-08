<?php

namespace DragonFly\Resource;

use DragonFly\Manager\LogManager;
use DragonFly\World\Vector;
use Exception;

class Animation
{

    /**
     * Curent index frame for sprite.
     *
     * @var int
     */
    private $index;

    /**
     * Sprite name in ResourceManager.
     *
     * @var string
     */
    private $name;
    
    /**
     * Slowdown counter.
     *
     * @var int
     */
    private $slowdownCount;

    /**
     * Sprite associated with animation.
     *
     * @var Sprite
     */
    private $sprite;

    /**
     * Instantiate class and properties.
     */
    public function __construct()
    {
        $this->setIndex(0);
        $this->setSlowdownCount(0);
    }

    /**
     * Destroy class and properties.
     */
    public function __destruct()
    {
        unset($this->index);
        unset($this->name);
        unset($this->slowdownCount);
        unset($this->sprite);
    }

    /**
     * Convert the Animation into a string (JSON).
     *
     * @return string
     */
    public function __toString(): ?string
    {
        try {
            $animation = "{";
            $animation .= "\"name\":\"{$this->getName()}\",";
            $animation .= "\"index\":{$this->getIndex()},";
            $animation .= "\"slowdownCount\":{$this->getSlowdownCount()},";
            $animation .= "\"sprite\":{$this->getSprite()}";
            $animation .= "}";
            return $animation;
        }
        catch (Exception $e) {
            LogManager::getInstance()->error($e);
        }
        return null;
    }

    /**
     * Draw single frame centered at position (x, y).
     * Drawing accounts for slowdown, and advances Sprite frame.
     *
     * @param Vector $position
     * @return boolean
     */
    public function draw(Vector $position): bool
    {
        if (is_null($this->sprite)) {
            throw new Exception("Can not draw animation sprite because sprite does not exist.");
        }
        $draw = $this->sprite->draw($this->index, $position);
        if ($this->slowdownCount > -1) {
            $this->setSlowdownCount($this->getSlowdownCount() + 1);
            if ($this->getSlowdownCount() >= $this->sprite->getSlowdown()) {
                $this->setSlowdownCount(0);
                $this->setIndex($this->getIndex() + 1);
                if ($this->getIndex() >= $this->sprite->getFrameCount()) {
                    $this->setIndex(0);
                }
            }
        }
        return $draw;
    }

    /**
     * Get index of the current sprite frame to be displayed.
     *
     * @return integer
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Get sprite name (in ResourceManager).
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get animation slowdown count (-1 means stop animation).
     *
     * @return integer
     */
    public function getSlowdownCount(): int
    {
        return $this->slowdownCount;
    }

    /**
     * Get assoicated sprite.
     *
     * @return Sprite
     */
    public function getSprite(): ?Sprite
    {
        return $this->sprite;
    }

    /**
     * Set index of the current sprite frame to be displayed.
     *
     * @param integer $index
     * @return void
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    /**
     * Set sprite name (in ResourceManager).
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Set animation slowdown count (-1 means stop animation).
     *
     * @param integer $slowdownCount
     * @return void
     */
    public function setSlowdownCount(int $slowdownCount): void
    {
        $this->slowdownCount = $slowdownCount;
    }

    /**
     * Set assoicated sprite.
     *
     * @param Sprite $sprite
     * @return void
     */
    public function setSprite(Sprite $sprite): void
    {
        $this->sprite = $sprite;
    }
}
