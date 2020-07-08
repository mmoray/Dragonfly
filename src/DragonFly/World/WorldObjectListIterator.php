<?php

namespace DragonFly\World;

use DragonFly\Manager\LogManager;
use Exception;

class WorldObjectListIterator
{
    /**
     * Index into the list.
     *
     * @var int
     */
    private $index;

    /**
     * WorldObjectList itterating over.
     *
     * @var WorldObjectList
     */
    private $objectList;

    /**
     * Keys for the WorldObjectList.
     *
     * @var array
     */
    private $objectListKeys;

    /**
     * Instantiate class and properties.
     *
     * @param WorldObjectList $objectList
     */
    public function __construct(WorldObjectList $objectList)
    {
        $this->first();
        $this->objectList = $objectList;
        $this->objectListKeys = $this->objectList->getKeys();
    }

    /**
     * Destory class and properties.
     */
    public function __destruct()
    {
        unset($this->index);
        unset($this->objectList);
        unset($this->objectListKeys);
    }

    /**
     * Convert the WorldObjectListIterator into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $iterator = "{";
        $iterator .= "\"index\":{$this->index},";
        $iterator .= "\"objectList\":{$this->objectList},";
        $iterator .= "\"objectListKeys\":[";
        for($i = 0; $i < count($this->objectListKeys); $i++) {
            $iterator .= "{$this->objectListKeys[$i]}";
            if ($i !== count($this->objectListKeys)) {
                $iterator .= ",";
            }
        }
        $iterator .= "]}";
        return $iterator;
    }

    /**
     * Get the current WorldObject for the index, NULL if done or empty.
     *
     * @return WorldObject|null
     */
    public function &currentItem(): ?WorldObject
    {
        if (!isset($this->objectListKeys[$this->index])) {
            throw new Exception("FATAL ERROR: Index out of bounds for keys array: {$this}");
        }
        try {
            return $this->objectList->getItem($this->objectListKeys[$this->index]);
        }
        catch (Exception $e) {
            LogManager::getInstance()->error($e);
        }
        return null;
    }

    /**
     * Set iterator to first item in the list.
     *
     * @return void
     */
    public function first(): void
    {
        $this->index = 0;
    }

    /**
     * Return true if at end of list.
     *
     * @return boolean
     */
    public function isDone(): bool
    {
        return $this->index === count($this->objectListKeys);
    }

    /**
     * Return iterator to the next item in the list.
     *
     * @return void
     */
    public function next(): void
    {
        if ($this->index < count($this->objectListKeys)) {
            $this->index++;
        }
    }
}
