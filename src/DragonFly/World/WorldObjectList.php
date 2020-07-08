<?php

namespace DragonFly\World;

use DragonFly\Manager\LogManager;
use Exception;

class WorldObjectList
{
    /**
     * Max amount of WorldObjects allowed in WorldObjectList.
     */
    const OBJECT_MAX = 5000;

    /**
     * Listing of WorldObjects.
     *
     * @var array
     */
    private $objects;

    /**
     * Instantiate class and properties.
     */
    public function __construct()
    {
        $this->objects = [];
    }

    /**
     * Destroy class and properties.
     */
    public function __destruct()
    {
        $this->clear();
        unset($this->objects);
    }

    /**
     * Convert the WorldObjectList into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $objects = "{";
        $objects .= "\"objects\":[";
        $itr = new WorldObjectListIterator($this);
        for ($itr->first(); !$itr->isDone(); $itr->next()) {
            $objects .= "{$itr->currentItem()}";
            if (!$itr->isDone()) {
                $objects .= ",";
            }
        }
        $objects .= "]}";
        return $objects;
    }

    /**
     * Append the specified WorldObjectsList.
     *
     * @param WorldObjectList $objectList
     * @return WorldObjectList
     */
    public function append(WorldObjectList $objectList): WorldObjectList
    {
        $itr = new WorldObjectListIterator($objectList);
        for ($itr->first(); !$itr->isDone(); $itr->next()) {
            try {
                $item = &$itr->currentItem();
                if (!is_null($item)) {
                    $this->insert($item);
                }
            }
            catch (Exception $e) {
                LogManager::getInstance()->warning($e);
            }
        }
        return $this;
    }

    /**
     * Clear list (setting count to 0).
     *
     * @return void
     */
    public function clear(): void
    {
        $itr = new WorldObjectListIterator($this);
        for ($itr->first(); !$itr->isDone(); $itr->next()) {
            try {
                $item = &$itr->currentItem();
                if (!is_null($item)) {
                    $this->remove($item);
                }
            }
            catch (Exception $e) {
                LogManager::getInstance()->warning($e);
            }
        }
    }

    /**
     * Return count of number of WorldObjects in list.
     *
     * @return integer
     */
    public function getCount(): int
    {
        return count($this->objects);
    }

    /**
     * Get the array keys for the WorldObjects in list.
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->objects);
    }

    /**
     * Get a WorldObject from the WorldObject list.
     *
     * @param integer $index
     * @return WorldObject
     * @throws Exception Invalid index supplied.
     */
    public function &getItem(int $index): WorldObject
    {
        if (!isset($this->objects[$index])) {
            throw new Exception("Invalid index supplied: {$index}");
        }
        return $this->objects[$index];
    }

    /**
     * Insert a WorldObject into the WorldObject list.
     *
     * @param WorldObject $object
     * @return void
     */
    public function insert(WorldObject &$object): void
    {
        if (array_search($object, $this->objects, true) !== false) {
            throw new Exception("WorldObject {$object->getType()}({$object->getId()}) already exists in WorldObjectList");
        }
        $this->objects[] = $object;
    }

    /**
     * Return true if list is empty, else false.
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return empty($this->objects);
    }

    /**
     * Return true if list is full, else false.
     *
     * @return boolean
     */
    public function isFull(): bool
    {
        return count($this->objects) === self::OBJECT_MAX;
    }

    /**
     * Remove a WorldObject from the WorldObject list.
     *
     * @param WorldObject $object
     * @return void
     * @throws Exception WorldObjectList is not set or WorldObject is not in WorldObjectList.
     */
    public function remove(WorldObject &$object): void
    {
        if (!isset($this->objects)) {
            throw new Exception("WorldObjectList has been destructed");
        }
        if (($key = array_search($object, $this->objects, true)) === false) {
            throw new Exception("WorldObject {$object->getType()}({$object->getId()}) does not exist in WorldObjectList");
        }
        unset($this->objects[$key]);
    }
}
