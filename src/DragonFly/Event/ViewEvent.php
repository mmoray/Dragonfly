<?php

namespace DragonFly\Event;

class ViewEvent extends Event
{
    /**
     * Tag to associate.
     *
     * @var string
     */
    private $tag;

    /**
     * Value for view.
     *
     * @var int
     */
    private $value;

    /**
     * True if change in value, otherwise replace value.
     *
     * @var bool
     */
    private $delta;

    /**
     * Instantiate class and properties.
     *
     * @param string $tag
     * @param integer $value
     * @param boolean $delta
     */
    public function __construct(...$params)
    {
        $tag = isset($params[0]) ? $params[0] : self::EVENT_VIEW;
        $value = isset($params[1]) && is_int($params[1]) ? $params[1] : 0;
        $delta = isset($params[2]) && is_bool($params[2]) ? $params[2] : false;
        parent::__construct(self::EVENT_VIEW);
        $this->setTag($tag);
        $this->setValue($value);
        $this->setDelta($delta);
    }

    /**
     * Destory class and properties.
     */
    public function __destruct()
    {
        unset($this->tag);
        unset($this->value);
        unset($this->delta);
        parent::__destruct();
    }

    /**
     * Convert the ViewEvent into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $view = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $view .= "\"tag\":\"{$this->getTag()}\"";
        $view .= "\"value\":{$this->getValue()}";
        $view .= sprintf("\"value\":%s", $this->getDelta() ? "true" : "false");
        $view .= "}";
        return $view;
    }
    
    /**
     * Get delta.
     *
     * @return boolean
     */
    public function getDelta(): bool
    {
        return $this->delta;
    }

    /**
     * Get tag.
     *
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * Get value.
     *
     * @return integer
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Set delta.
     *
     * @param boolean $delta
     * @return void
     */
    public function setDelta(bool $delta): void
    {
        $this->delta = $delta;
    }

    /**
     * Set tag.
     *
     * @param string $tag
     * @return void
     */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * Set value.
     *
     * @param integer $value
     * @return void
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }
}
