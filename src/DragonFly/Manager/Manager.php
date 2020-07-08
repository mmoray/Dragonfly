<?php

namespace DragonFly\Manager;

use DragonFly\Base\Singleton;
use DragonFly\Event\Event;
use DragonFly\World\WorldObject;
use DragonFly\World\WorldObjectList;
use DragonFly\World\WorldObjectListIterator;
use Exception;

class Manager extends Singleton
{
    const MAX_EVENTS = 100;
    const TYPE_DISPLAY = 'df::DisplayManager';
    const TYPE_GAME = 'df::GameManager';
    const TYPE_INPUT = 'df::InputManager';
    const TYPE_LOG = 'df::LogManager';
    const TYPE_MANAGER = 'df::Manager';
    const TYPE_RESOURCE = 'df::ResourceManager';
    const TYPE_WORLD = 'df::WorldManager';

    /**
     * Number of events.
     *
     * @var 
     */
    protected $eventCount;

    /**
     * List of events.
     *
     * @var array
     */
    protected $events;

    /**
     * Objects interested in event.
     *
     * @var array
     */
    protected $interested;

    /**
     * The single instance of the class.
     *
     * @var Singleton
     */
    protected static $instance;

    /**
     * True when started successfully.
     *
     * @var boolean
     */
    private $started;

    /**
     * Manager type identifier.
     *
     * @var string
     */
    protected $type;

    /**
     * Instantiate class and properties.
     */
    protected function __construct()
    {
        $this->eventCount = 0;
        $this->events = [];
        $this->interested = [];
        $this->started = false;
        $this->setType(self::TYPE_MANAGER);
    }

    /**
     * Destrory the class and properties.
     */
    public function __destruct()
    {
        for ($i = 0; $i < $this->eventCount; $i++) {
            unset($this->events[$i]);
            unset($this->interested[$i]);
        }
        unset($this->eventCount);
        unset($this->events);
        unset($this->interested);
        unset($this->started);
        unset($this->type);
    }

    /**
     * Convert the manager into a string (json).
     *
     * @return string
     */
    public function __toString(): string
    {
        if (!isset($this->type)) {
            return '';
        }
        $manager = "{";
        $manager .= "\"type\":\"{$this->type}\",";
        $manager .= "\"started\":{$this->started},";
        $manager .= "\"eventCount\":{$this->eventCount},";
        $manager .= "\"events\":[";
        for ($i = 0; $i < $this->eventCount; $i++) {
            $manager .= "\"{$this->events[$i]}\"";
            if ($i < $this->eventCount - 1) {
                $manager .= ",";
            }
        }
        $manager .= "],";
        $manager .= "'interested':[";
        for ($i = 0; $i < $this->eventCount; $i++) {
            $manager .= "{$this->interested[$i]}";
            if ($i < $this->eventCount - 1) {
                $manager .= ",";
            }
        }
        $manager .= "]}";
        return $manager;
    }

    /**
     * Send events to all interrested WorldObjects.
     *
     * @param Event $event
     * @return integer Count of number of events sent.
     */
    public function dispatch(Event $event): int
    {
        $count = 0;
        for ($i = 0; $i < $this->eventCount; $i++) {
            if ($this->events[$i] === $event->getType()) {
                $itr = new WorldObjectListIterator($this->interested[$i]);
                for ($itr->first(); !$itr->isDone(); $itr->next()) {
                    try {
                        $item = &$itr->currentItem();
                        if (!is_null($item) && $item->getActive()) {
                            $item->handle($event);
                            $count++;
                        }
                    }
                    catch (Exception $e) {
                        LogManager::getInstance()->error($e);
                    }
                }
                break;
            }
        }
        return $count;
    }

    /**
     * Return the one and only instance of the class.
     *
     * @return Singleton
     */
    public static function getInstance(): Singleton
    {
        if (!(self::$instance instanceof Manager)) {
            self::$instance = new Manager;
        }
        return self::$instance;
    }

    /**
     * Get type identifier of Manager.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Return true when startUp() was executed ok, otherwise false.
     *
     * @return boolean
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Check if event is handled by this manager.
     *
     * @param string $eventType
     * @return boolean True if event is handled, otherwise false.
     */
    public function isValid(string $eventType): bool
    {
        return false;
    }

    /**
     * Indicate interested in event.
     *
     * @param WorldObject $object
     * @param string $eventType
     * @return boolean
     */
    public function registerInterest(WorldObject &$object, string $eventType): bool
    {
        try {
            // EVENT TYPE ALREADY INSERTED
            for ($i = 0; $i < $this->eventCount; $i++) {
                if ($this->events[$i] === $eventType) {
                    $this->interested[$i]->insert($object);
                    return true;
                }
            }
            // INSERT NEW EVENT TYPE
            if ($this->eventCount < self::MAX_EVENTS) {
                $this->events[$this->eventCount] = $eventType;
                if (empty($this->interested[$this->eventCount])) {
                    $this->interested[$this->eventCount] = new WorldObjectList;
                }
                else {
                    $this->interested[$this->eventCount]->clear();
                }
                $this->interested[$this->eventCount]->insert($object);
                $this->eventCount++;
                return true;
            }
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }

    /**
     * Set type identifier of Manager.
     *
     * @param string $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Shutdown Manager.
     *
     * @return void
     */
    public function shutDown(): void
    {
        LogManager::getInstance()->info("Shutting down {$this->getType()}: {$this}");
        $this->started = false;
    }

    /**
     * Startup Manager.
     *
     * @return void 
     */
    public function startUp(): void
    {
        LogManager::getInstance()->info("Starting up {$this->getType()}: {$this}");   
        $this->started = true;
    }

    /**
     * Indicate no more interest in event.
     *
     * @param WorldObject $object
     * @param string $eventType
     * @return boolean
     */
    public function unregisterInterest(WorldObject &$object, string $eventType): bool
    {
        if (!isset($this->eventCount)) {
            throw new Exception("Manager has been destructed");
        }
        try {
            for($i = 0; $i < $this->eventCount; $i++) {
                if ($this->events[$i] === $eventType) {
                    $this->interested[$i]->remove($object);
                    if ($this->interested[$i]->isEmpty()) {
                        for ($j = $i + 1; $j < $this->eventCount; $j++) {
                            $this->events[$j - 1] = $this->events[$j];
                            $this->interested[$j - 1] = $this->interested[$j];
                        }
                        $this->eventCount--;
                    }
                    return true;
                }
            }
        }
        catch (Exception $e) {
            LogManager::getInstance()->warning($e);
        }
        return false;
    }
}
