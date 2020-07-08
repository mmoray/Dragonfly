<?php

namespace DragonFly\Manager;

use DragonFly\Base\Singleton;
use DragonFly\Base\Utility;
use DragonFly\Event\CollisionEvent;
use DragonFly\Event\OutEvent;
use DragonFly\World\Box;
use DragonFly\World\SceneGraph;
use DragonFly\World\Vector;
use DragonFly\World\ViewObject;
use DragonFly\World\WorldObject;
use DragonFly\World\WorldObjectList;
use DragonFly\World\WorldObjectListIterator;
use Exception;

class WorldManager extends Manager
{
    /**
     * World boundary.
     *
     * @var Box
     */
    private $boundary;

    /**
     * All WorldObjects in world to delete.
     *
     * @var WorldObjectList
     */
    private $deletes;

    /**
     * WorldObject view is following.
     *
     * @var WorldObject
     */
    private $following;

    /**
     * Storage for all objects.
     *
     * @var SceneGraph
     */
    private $objects;

    /**
     * Player view of game world.
     *
     * @var Box
     */
    private $view;

    /**
     * View center screen range.
     *
     * @var Vector
     */
    private $viewSlack;
    
    /**
     * The single instance of the class.
     *
     * @var Singleton
     */
    protected static $instance;

    /**
     * Instantiate class and properties.
     */
    protected function __construct()
    {
        parent::__construct();
        $this->setBoundary(new Box);
        $this->setFollowing();
        $this->setType(self::TYPE_WORLD);
        $this->setView(new Box);
        $this->setViewSlack(new Vector);
        $this->deletes = null;
        $this->objects = null;
    }

    /**
     * Destory class and properties.
     */
    public function __destruct()
    {
        unset($this->boundary);
        unset($this->deletes);
        unset($this->view);
        $itr = new WorldObjectListIterator($this->getAllObjects(true));
        for($itr->first(); !$itr->isDone(); $itr->next()) {
            $item = &$itr->currentItem();
            if (!is_null($item)) {
                $this->objects->removeObject($item);
            }
        }
        unset($itr);
        unset($this->objects);
        parent::__destruct();
    }

    /**
     * Convert the WorldManager into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $world = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $world .= "\"boundary\":{$this->getBoundary()},";
        $world .= "\"deletes\":{$this->deletes},";
        $world .= "\"following\":{$this->following},";
        $world .= "\"objects\":{$this->objects},";
        $world .= "\"view\":{$this->getView()},";
        $world .= "\"viewSlack\":{$this->getViewSlack()}";
        $world .= "}";
        return $world;;
    }

    /**
     * Draw the world to the screen.
     *
     * @return void
     */
    public function draw(): void
    {
        $itr = new WorldObjectListIterator($this->objects->getAllObjectsSortedByAltitude());
        for ($itr->first(); !$itr->isDone(); $itr->next()) {
            $item = &$itr->currentItem();
            if (!is_null($item)) {
                $tempBox = Utility::getWorldBox($item);
                if (Utility::boxesIntersect($tempBox, $this->getView()) || $item instanceof ViewObject) {
                    $item->draw();
                }
            }
        }
    }

    /**
     * Return list of all WorldObjects in world.
     *
     * @return WorldObjectList
     */
    public function getAllObjects(bool $inactive = false): WorldObjectList
    {
        $objects = $this->objects->getActiveObjects();
        if ($inactive) {
            $objects->append($this->objects->getInactiveObjects());
        }
        return $objects;
    }

    /**
     * Get game world boundary.
     *
     * @return Box
     */
    public function getBoundary(): Box
    {
        return $this->boundary;
    }

    /**
     * Return list of objects collided with at position.
     * Collisions only with solid objects.
     * Do not consider self.
     *
     * @param WorldObject $object
     * @param Vector $position
     * @return WorldObjectList
     */
    public function getCollisions(WorldObject &$object, Vector $position): WorldObjectList
    {
        $collisions = new WorldObjectList;
        $itr = new WorldObjectListIterator($this->objects->getSolidObjects());
        $oBox = Utility::getWorldBox($object);
        for($itr->first(); !$itr->isDone(); $itr->next()) {
            $item = &$itr->currentItem();
            if (!is_null($item) && $object->getId() !== $item->getId()) {
                $iBox = Utility::getWorldBox($item);
                if (Utility::boxesIntersect($oBox, $iBox) && $item->isSolid()) {
                    try {
                        $collisions->insert($item);
                    }
                    catch (Exception $e) {
                        LogManager::getInstance()->warning($e);
                    }
                }
            }
        }
        return $collisions;
    }

    /**
     * Return the one and only instance of the class.
     *
     * @return Singleton
     */
    public static function getInstance(): Singleton
    {
        if (!(self::$instance instanceof WorldManager)) {
            self::$instance = new WorldManager;
        }
        return self::$instance;
    }

    /**
     * Return reference to the SceneGraph.
     *
     * @return SceneGraph
     */
    public function &getObjects(): SceneGraph
    {
        return $this->objects;
    }
    
    /**
     * Get player view of game world.
     *
     * @return Box
     */
    public function getView(): Box
    {
        return $this->view;
    }

    /**
     * Get view center screen range.
     *
     * @return Vector
     */
    public function getViewSlack(): Vector
    {
        return $this->viewSlack;
    }

    /**
     * Insert WorldObject into world.
     *
     * @param WorldObject $object
     * @return void
     */
    public function insertObject(WorldObject &$object): void
    {
        $this->objects->insertObject($object);
    }

    /**
     * Indicate WorldObject is to be deleted at end of current game loop.
     *
     * @param WorldObject $object
     * @return void
     */
    public function markForDelete(WorldObject &$object): void
    {
        try {
            $this->deletes->insert($object);
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
    }

    /**
     * Return list of all WorldObjects in world matching type.
     *
     * @param string $type
     * @return WorldObjectList
     */
    public function objectsOfType(string $type): WorldObjectList
    {
        $ofType = new WorldObjectList;
        $itr = new WorldObjectListIterator($this->getAllObjects());
        for($itr->start(); !$itr->isDone(); $itr->next()) {
            $item = &$itr->currentItem();
            if (!is_null($item) && $item->getType() === $type) {
                $ofType->insert($item);
            }
        }
        return $ofType;
    }

    /**
     * Move object.
     * If collision with solid, send collision events.
     * If no collision with solid, move ok.
     * If all collided objects soft, move ok.
     * If object is spectral, move ok.
     * If move ok, move.
     *
     * @param WorldObject $object
     * @param Vector $position
     * @return boolean
     */
    public function moveObject(WorldObject &$object, Vector $position): bool
    {
        $move = true;
        if ($object->isSolid()) {
            $collisions = $this->getCollisions($object, $position);
            if (!$collisions->isEmpty()) {
                $event = new CollisionEvent($object, null, $position);
                $itr = new WorldObjectListIterator($collisions);
                for ($itr->first(); !$itr->isDone(); $itr->next()) {
                    $item = &$itr->currentItem();
                    if (!is_null($item)) {
                        $event->setOffended($item);
                        $object->handle($event);
                        $item->handle($event);
                        if ($object->getSolidness() === WorldObject::SOLIDNESS_HARD && $item->getSolidness() === WorldObject::SOLIDNESS_HARD) {
                            $move = false;
                        }
                        if ($object->getNoSoft() && $item->getSolidness() === WorldObject::SOLIDNESS_SOFT) {
                            $move = false;
                        }
                    }
                }
            }
        }
        if ($move) {
            $original = Utility::getWorldBox($object);
            $object->setPosition($position);
            $updated = Utility::getWorldBox($object);

            if (Utility::boxesIntersect($original, $this->view) && !Utility::boxesIntersect($updated, $this->view)) {
                $event = new OutEvent;
                $object->handle($event);
            }

            if (!is_null($this->following) && $this->following->getId() === $object->getId()) {
                $viewCenterX = $this->view->getCorner() + $this->view->getHorizontal() / 2;
                $viewCenterY = $this->view->getCorner() + $this->view->getVertical() / 2;

                $bottom = $viewCenterY - $this->view->getVertical() * $this->viewSlack->getY() / 2;
                $left = $viewCenterX - $this->view->getHorizontal() * $this->viewSlack->getX() / 2;
                $right = $viewCenterX + $this->view->getHorizontal() * $this->viewSlack->getX() / 2;
                $top = $viewCenterY + $this->view->getVertical * $this->viewSlack->getY() / 2;

                $newPosition = $object->getPosition();
                if ($newPosition->getX() < $left) {
                    $viewCenterX -= $left - $newPosition->getX();
                }
                else if ($newPosition->getX() > $right) {
                    $viewCenterX += $newPosition->getX() - $right;
                }
                if ($newPosition->getY() < $top) {
                    $viewCenterY -= $top - $newPosition->getY();
                }
                else if ($newPosition->getY() > $bottom) {
                    $viewCenterY += $newPosition->getY() - $bottom;
                }

                $this->setViewPosition(new Vector($viewCenterX, $viewCenterY));
            }
        }
        return $move;
    }

    /**
     * Remove WorldObject from world.
     *
     * @param WorldObject $object
     * @return void
     */
    public function removeObject(WorldObject &$object): void
    {
        if (!isset($this->objects)) {
            throw new Exception("WorldManager has been destructed");
        }
        $this->objects->removeObject($object);
    }

    /**
     * Set game world boundary.
     *
     * @param Box $boundary
     * @return void
     */
    public function setBoundary(Box $boundary): void
    {
        $this->boundary = $boundary;
    }

    /**
     * Set view to follow object.
     * Set to NULL to stop following.
     *
     * @param WorldObject $object
     * @return boolean
     */
    public function setFollowing(WorldObject &$object = null): bool
    {
        if (is_null($object)) {
            $this->following = $object;
            return true;
        }
        
        $itr = new WorldObjectListIterator($this->getAllObjects());
        for ($itr->first(); !$itr->isDone(); $itr->next()) {
            if (!is_null($item) && $itr->currentItem()->getId() === $object->getId()) {
                $this->following = $object;
                $this->setViewPosition($this->following->getPosition());
                return true;
            }
        }

        return false;
    }

    /**
     * Set play view of game world.
     *
     * @param Box $view
     * @return void
     */
    public function setView(Box $view): void
    {
        $this->view = $view;
    }

    /**
     * Set view center screen range.
     *
     * @param Vector $viewSlack
     * @return void
     */
    public function setViewSlack(Vector $viewSlack): void
    {
        $this->viewSlack = $viewSlack;
    }

    /**
     * Set view to center window on position.
     * View edge will not go beyond world boundary.
     *
     * @param Vector $position
     * @return void
     */
    public function setViewPosition(Vector $position): void
    {
        $x = $position->getX() - $this->view->getHorizontal() / 2;
        if ($x < 0) {
            $x = 0;
        }
        else if ($x + $this->view->getHorizontal() > $this->boundary->getHorizontal()) {
            $x = $this->boundary->getHorizontal() - $this->view->getHorizontal();
        }

        $y = $position->getY() - $this->view->getVertical() / 2;
        if ($y < 0) {
            $y = 0;
        }
        else if ($y + $this->view->getVertical() > $this->boundary->getVertical()) {
            $y = $this->boundary->getVertical() - $this->view->getVertical();
        }

        $this->view->setCorner(new Vector($x, $y));
    }

    /**
     * Shutdown WorldManager.
     *
     * @return void
     */
    public function shutDown(): void
    {
        parent::shutDown();
        $this->deletes->clear();
        $itr = new WorldObjectListIterator($this->getAllObjects(true));
        for($itr->first(); !$itr->isDone(); $itr->next()) {
            $item = &$itr->currentItem();
            if (!is_null($item)) {
                $this->objects->removeObject($item);
            }
        }
    }

    /**
     * Startup WorldManager.
     *
     * @return void
     */
    public function startUp(): void
    {
        parent::startUp();
        $this->deletes = new WorldObjectList;
        $this->boundary->setHorizontal(DisplayManager::getInstance()->getHorizontal());
        $this->boundary->setVertical(DisplayManager::getInstance()->getVertical());
        $this->objects = new SceneGraph;
    }

    /**
     * Update world. Delete objects marked for deletion.
     *
     * @return void
     */
    public function update(): void
    {
        $itr = new WorldObjectListIterator($this->objects->getActiveObjects());
        for($itr->first(); !$itr->isDone(); $itr->next()) {
            $item = &$itr->currentItem();
            if (!is_null($item)) {
                $newPosition = $item->predictPosition();
                if ($newPosition->notEqual($item->getPosition())) {
                    $this->moveObject($item, $newPosition);
                }
            }
        }

        $itr = new WorldObjectListIterator($this->deletes);
        for ($itr->first(); !$itr->isDone(); $itr->next()) {
            $item = &$itr->currentItem();
            if (!is_null($item)) {
                $this->removeObject($item);
            }
        }
        $this->deletes->clear();
    }
}
