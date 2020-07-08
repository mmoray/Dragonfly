<?php

namespace DragonFly\World;

use DragonFly\Manager\LogManager;
use Exception;

class SceneGraph
{
    /**
     * All active objects.
     *
     * @var WorldObjectList
     */
    private $activeObjects;

    /**
     * All inactive objects.
     *
     * @var WorldObjectList
     */
    private $inactiveObjects;

    /**
     * Solid objects.
     *
     * @var WorldObjectList
     */
    private $solidObjects;

    /**
     * Visible objects.
     *
     * @var array
     */
    private $visibleObjects;

    /**
     * Instantiate class and properties.
     */
    public function __construct()
    {
        $this->activeObjects = new WorldObjectList;
        $this->inactiveObjects = new WorldObjectList;
        $this->solidObjects = new WorldObjectList;
        $this->visibleObjects = [];
        for ($i = 0; $i < WorldObject::MAX_ALTITUDE; $i++) {
            $this->visibleObjects[$i] = new WorldObjectList;
        }
    }

    /**
     * Destroy class and properties.
     */
    public function __destruct()
    {
        $this->activeObjects->clear();
        unset($this->activeObjects);
        $this->inactiveObjects->clear();
        unset($this->inactiveObjects);
        $this->solidObjects->clear();
        unset($this->solidObjects);
        foreach($this->visibleObjects AS $index => $objects) {
            $this->visibleObjects[$index]->clear();
            unset($this->visibleObjects[$index]);
        }
        unset($this->visibleObjects);
    }

    /**
     * Convert the SceneGraph into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $sceneGraph = "{";
        $sceneGraph .= "\"activeObjects\":{$this->getActiveObjects()},";
        $sceneGraph .= "\"inactiveObjects\":{$this->getInactiveObjects()},";
        $sceneGraph .= "\"solidObjects\":{$this->getSolidObjects()},";
        $sceneGraph .= "\"visibleObjects\":[";
        for ($i = 0; $i < WorldObject::MAX_ALTITUDE; $i++) {
            $sceneGraph .= "{$this->getVisibleObjects($i)}";
            if ($i < WorldObject::MAX_ALTITUDE - 1) {
                $sceneGraph .= ",";
            }
        }
        $sceneGraph .= "]}";
        return $sceneGraph;
    }

    /**
     * Return all active objects. Empty list if none.
     *
     * @return WorldObjectList
     */
    public function getActiveObjects(): WorldObjectList
    {
        return $this->activeObjects;
    }

    /**
     * Get all objects sorted by altitude.
     *
     * @return WorldObjectList
     */
    public function getAllObjectsSortedByAltitude(): WorldObjectList
    {
        $sortedObjects = new WorldObjectList;
        foreach($this->visibleObjects AS $objects) {
            $sortedObjects->append($objects);
        }
        return $sortedObjects;
    }

    /**
     * Get all inactive objects. Empty list if none.
     *
     * @return WorldObjectList
     */
    public function getInactiveObjects(): WorldObjectList
    {
        return $this->inactiveObjects;
    }

    /**
     * Get solid objects.
     *
     * @return WorldObjectList
     */
    public function getSolidObjects(): WorldObjectList
    {
        return $this->solidObjects;
    }

    /**
     * Get visible objects at specified altitude.
     *
     * @param integer $altitude
     * @return WorldObjectList
     * @throws Exception Invalid altitude supplied.
     */
    public function getVisibleObjects(int $altitude): WorldObjectList
    {
        if ($altitude < 0 && $altitude >= WorldObject::MAX_ALTITUDE) {
            throw new Exception("Invalid alitiude supplied: {$altitude}");
        }
        return $this->visibleObjects[$altitude];
    }

    /**
     * Insert WorldObject into SceneGraph.
     *
     * @param WorldObject $object
     * @return void
     */
    public function insertObject(WorldObject &$object): void
    {
        $status = "Insert {$object->getType()}({$object->getId()}) into ";
        if ($object->getActive()) {
            $lists = [];
            if ($this->insertActiveObject($object)) {
                $lists[] = "active";
            }
            if ($object->isSolid() && $this->insertSolidObject($object)) {
                $lists[] = "solid";
            }
            if ($this->insertVisibleObject($object)) {
                $lists[] = "visible";
            }
            if (empty($lists)) {
                $status .= "no";
            }
            else {
                $last = array_pop($lists);
                $status .= empty($lists) ? $last : (count($lists) > 1 ? sprintf("%s, and %s", implode(", ", $lists), $last) : sprintf("%s and %s", $lists[0], $last));
            }
        }
        else if ($this->insertInactiveObject()){
            $status .= "inactive";
        }
        else {
            $status .= "no";
        }
        $status .= " WorldObjectList";
        LogManager::getInstance()->info($status);
    }

    /**
     * Remove WorlObject from SceneGraph.
     *
     * @param WorldObject $object
     * @return void
     */
    public function removeObject(WorldObject &$object): void
    {
        $status = "Remove {$object->getType()}({$object->getId()}) from ";
        try {
            $lists = [];
            if ($object->getActive() && $this->removeActiveObject($object)) {
                $lists[] = "active";
            }
            else if ($this->removeInactiveObject($object)) {
                $lists[] = "inactive";
            }
            if ($object->isSolid() && $this->removeSolidObject($object)) {
                $lists[] = "solid";   
            }
            if ($this->removeVisibleObject($object)) {
                $lists[] = "visible";
            }
            if (empty($lists)) {
                $status .= "no";
            }
            else {
                $last = array_pop($lists);
                $status .= empty($lists) ? $last : (count($lists) > 1 ? sprintf("%s, and %s", implode(", ", $lists), $last) : sprintf("%s and %s", $lists[0], $last));
            }
            $status .= " WorldObjectList";
            LogManager::getInstance()->info($status);
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
    }

    /**
     * Re-position object in SceneGraph to new activeness.
     *
     * @param WorldObject $object
     * @param boolean $active
     * @return void
     */
    public function updateActive(WorldObject &$object, bool $active): void
    {
        try {
            if ($object->getActive() !== $active) {
                if ($object->getActive()) {
                    $this->insertInactiveObject($object);
                    $this->removeActiveObject($object);
                    if ($object->isSolid()) {
                        $this->removeSolidObject($object);
                    }
                    $this->removeVisibleObject($object);
                }
                else {
                    $this->insertActiveObject($object);
                    if ($object->isSolid()) {
                        $this->insertSolidObject($object);
                    }
                    $this->insertVisibleObject($object);
                    $this->removeInactiveObject($object);
                }
            }
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
    }

    /**
     * Re-position object in SceneGraph to new altitude.
     *
     * @param WorldObject $object
     * @param integer $altitude
     * @return void
     */
    public function updateAltitude(WorldObject &$object, int $altitude): void
    {
        try {
            if ($object->getAltitude() !== $altitude) {
                $this->insertVisibleObject($object, $altitude);
                $this->removeVisibleObject($object);
            }
        }
        catch(Exception $e) {
            LogManager::getInstance()->warning($e);
        }
    }

    /**
     * Re-position object in SceneGraph to new solidness.
     *
     * @param WorldObject $object
     * @param string $solidness
     * @return void
     */
    public function updateSolidness(WorldObject &$object, string $solidness): void
    {
        try {
            if ($object->getSolidness() !== $solidness) {
                if ($object->isSolid()) {
                    $this->removeSolidObject($object);
                }
                if (in_array($solidness, [WorldObject::SOLIDNESS_HARD, WorldObject::SOLIDNESS_SOFT])) {
                    $this->insertSolidObject($object);
                }
            }
        }
        catch(Exception $e) {
            LogManager::getInstance()->warning($e);
        }
    }

    /**
     * Re-position object in SceneGraph based on visibility.
     *
     * @param WorldObject $object
     * @param boolean $visible
     * @return void
     */
    public function updateVisible(WorldObject &$object, bool $visible): void
    {
        try {
            if ($object->getVisible() !== $visible) {
                if ($object->getVisible()) {
                    $this->removeVisibleObject($object);
                }
                else {
                    $this->insertVisibleObject($object);
                }
            }
        }
        catch(Exception $e) {
            LogManager::getInstance()->warning($e);
        }
    }

    /**
     * Insert WorldObject into active WorldObjectList.
     *
     * @param WorldObject $object
     * @return boolean
     */
    private function insertActiveObject(WorldObject &$object): bool
    {
        try {
            $this->activeObjects->insert($object);
            return true;
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }

    /**
     * Insert WorldObject into inactive WorldObjectList.
     *
     * @param WorldObject $object
     * @return boolean
     */
    private function insertInactiveObject(WorldObject &$object): bool
    {
        try {
            $this->inactiveObjects->insert($object);
            return true;
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }

    /**
     * Insert WorldObject into solid WorldObjectList.
     *
     * @param WorldObject $object
     * @return boolean
     */
    private function insertSolidObject(WorldObject &$object): bool
    {
        try {
            $this->solidObjects->insert($object);
            return true;
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }

    /**
     * Insert WorldObject into visible WorldObjectList.
     *
     * @param WorldObject $object
     * @param integer $altitude
     * @return boolean
     */
    private function insertVisibleObject(WorldObject &$object, int $altitude = null): bool
    {
        try {
            if (is_null($altitude)) {
                $altitude = $object->getAltitude();
            }
            $this->visibleObjects[$altitude]->insert($object);
            return true;
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }

    /**
     * Remove WorldObject from active WorldObjectList.
     *
     * @param WorldObject $object
     * @return boolean
     * @throws Exception Active WorldObjectList is not set.
     */
    private function removeActiveObject(WorldObject &$object): bool
    {
        if (!isset($this->activeObjects)) {
            throw new Exception("Active WorldObjectList has been destructed");
        }
        try {
            $this->activeObjects->remove($object);
            return true;
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }

    /**
     * Remove WorldObject from inactive WorldObjectList.
     *
     * @param WorldObject $object
     * @return boolean
     * @throws Exception Inactive WorldObjectList is not set.
     */
    private function removeInactiveObject(WorldObject &$object): bool
    {
        if (!isset($this->inactiveObjects)) {
            throw new Exception("Inactive WorldObjectList has been destructed");
        }
        try {
            $this->inactiveObjects->remove($object);
            return true;
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }

    /**
     * Remove WorldObject from solid WorldObjectList.
     *
     * @param WorldObject $object
     * @return boolean
     * @throws Exception Solid WorldObjectList is not set.
     */
    private function removeSolidObject(WorldObject &$object): bool
    {
        if (!isset($this->solidObjects)) {
            throw new Exception("Solid WorldObjectList has been destructed");
        }
        try {
            $this->solidObjects->remove($object);
            return true;
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }

    /**
     * Remove WorldObject from visible WorldObjectList.
     *
     * @param WorldObject $object
     * @return boolean
     * @throws Exception Visible WorldObjectList is not set.
     */
    private function removeVisibleObject(WorldObject &$object, int $altitude = null): bool
    {
        if (!isset($this->visibleObjects) || !isset($this->visibleObjects[$object->getAltitude()])) {
            throw new Exception("Visible WorldObjectList has been destructed");
        }
        try {
            if (is_null($altitude)) {
                $altitude = $object->getAltitude();
            }
            $this->visibleObjects[$altitude]->remove($object);
            return true;
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }
}
