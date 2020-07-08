<?php

namespace DragonFlyEgg\Objects;

use DragonFly\Event\Event;
use DragonFly\Event\KeyboardEvent;
use DragonFly\Manager\LogManager;
use DragonFly\Manager\DisplayManager;
use DragonFly\Manager\GameManager;
use DragonFly\Manager\WorldManager;
use DragonFly\World\Vector;
use DragonFly\World\WorldObject;

class Saucer extends WorldObject
{
    const TYPE_SAUCER = 'dfe::saucer';

    /**
     * Max amount of steps for saucer to be alive.
     * Unlimited if set to 0.
     *
     * @var int
     */
    private $maxStepCount;

    /**
     * Determine if moving left or right
     *
     * @var bool
     */
    private $moveLeft;

    /**
     * Instantiate class and properties
     *
     * @param float $x
     * @param float $y
     * @param int $altitude
     * @param int $maxStepCount
     */
    public function __construct(...$params)
    {
        $type = self::TYPE_SAUCER;
        $x = isset($params[0]) ? $params[0] : 0;
        $y = isset($params[1]) ? $params[1] : 0;
        $altitude = isset($params[2]) ? $params[2] : 0;
        $maxStepCount = isset($params[3]) ? $params[3] : 0;
        $this->setMaxStepCount($maxStepCount);
        $this->setMoveLeft(rand() % 2);
        parent::__construct($type, $x, $y, $altitude);
        LogManager::getInstance()->info("Crate Saucer: {'id': {$this->id}, 'type': '{$this->type}', 'x': {$this->position->getX()}, 'y': {$this->position->getY()}, 'altitude': {$this->altitude}, 'maxStepCount': {$this->maxStepCount}}");
    }

    /**
     * Destroy class and properties.
     */
    public function __destruct()
    {
        LogManager::getInstance("Destructing Saucer {$this->id}");
        unset($this->maxStepCount);
        parent::__destruct();

    }

    /**
     * Remove this saucer from the game.
     *
     * @return void
     */
    public function die()
    {
        $wm = WorldManager::getInstance();
        $wm->markForDelete($this);
    }

    /**
     * Draw the saucer to the screen.
     *
     * @return void
     */
    public function draw(): void
    {
        $dm = DisplayManager::getInstance();
        $dm->drawChar($this->position, 'S');
    }

    /**
     * Get max amount of steps for saucer to be alive. 
     *
     * @return integer
     */
    public function getMaxStepCount(): int
    {
        return $this->maxStepCount;
    }

    /**
     * Get if the saucer is moving left.
     *
     * @return boolean
     */
    public function getMoveLeft(): bool
    {
        return $this->moveLeft;
    }

    /**
     * Get if the saucer is moving right.
     *
     * @return boolean
     */
    public function getMoveRight(): bool
    {
        return !$this->getMoveLeft();
    }

    /**
     * Handle event (default is to ignore everything).
     *
     * @param Event $event
     * @return boolean Return false if ignored, else true if handled.
     */
    public function handle(Event $event): bool
    {
        switch ($event->getType()) {
            case Event::EVENT_STEP:
                if ($this->maxStepCount > 0){
                    if ($event->getStepCount() === $this->maxStepCount) {
                        $this->die();
                        LogManager::getInstance()->info("Remove Saucer: {'id': {$this->id}, 'type': '{$this->type}', 'x': {$this->position->getX()}, 'y': {$this->position->getY()}, 'altitude': {$this->altitude}, 'maxStepCount': {$this->maxStepCount}}");
                    }
                    else if ($event->getStepCount() % 5 === 0) {
                        if ($this->getMoveLeft()) {
                            $this->setMoveRight();
                        }
                        else {
                            $this->setMoveLeft();
                        }
                    }
                    $this->move();
                    LogManager::getInstance()->info("Saucer: {'id': {$this->id}, 'type': '{$this->type}', 'x': {$this->position->getX()}, 'y': {$this->position->getY()}, 'altitude': {$this->altitude}, 'maxStepCount': {$this->maxStepCount}}");
                }
                if ($this->position->getX() <= 1 || $this->position->getX() >= DisplayManager::WINDOW_HORIZONTAL_PIXEL_DEFAULT - 1) {
                    GameManager::getInstance()->setGameOver();
                }
                break;
            case Event::EVENT_KEYBOARD:
                switch ($event->getValue()) {
                    case KeyboardEvent::KEY_LOWER_A:
                    case KeyboardEvent::KEY_UPPER_A:
                        $this->setMoveLeft();
                        $this->move();
                        LogManager::getInstance()->info("Saucer: {'id': {$this->id}, 'type': '{$this->type}', 'x': {$this->position->getX()}, 'y': {$this->position->getY()}, 'altitude': {$this->altitude}, 'maxStepCount': {$this->maxStepCount}}");
                        if ($this->position->getX() <= 0) {
                            $this->die();
                            LogManager::getInstance()->info("Remove Saucer: {'id': {$this->id}, 'type': '{$this->type}', 'x': {$this->position->getX()}, 'y': {$this->position->getY()}, 'altitude': {$this->altitude}, 'maxStepCount': {$this->maxStepCount}}");
                            GameManager::getInstance()->setGameOver();
                        }
                        break;
                    case KeyboardEvent::KEY_LOWER_D:
                    case KeyboardEvent::KEY_UPPER_D:
                        $this->setMoveRight();
                        $this->move();
                        LogManager::getInstance()->info("Saucer: {'id': {$this->id}, 'type': '{$this->type}', 'x': {$this->position->getX()}, 'y': {$this->position->getY()}, 'altitude': {$this->altitude}, 'maxStepCount': {$this->maxStepCount}}");
                        if ($this->position->getX() >= DisplayManager::getInstance()->getHorizontal()) {
                            $this->die();
                            LogManager::getInstance()->info("Remove Saucer: {'id': {$this->id}, 'type': '{$this->type}', 'x': {$this->position->getX()}, 'y': {$this->position->getY()}, 'altitude': {$this->altitude}, 'maxStepCount': {$this->maxStepCount}}");
                            GameManager::getInstance()->setGameOver();
                        }
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
        return parent::handle($event);
    }

    /**
     * Move the saucer.
     *
     * @return void
     */
    public function move(): void
    {
        if ($this->getMoveLeft()) {
            $this->setAcceleration(new Vector(-.8, 0));
        }
        else {
            $this->setAcceleration(new Vector(.8, 0));
        }
        if ($this->getSpeed() > .75) {
            $this->setSpeed(.75);
        }
    }

    /**
     * Set max amount of steps for saucer to be alive.
     *
     * @param integer $maxStepCount
     * @return void
     */
    public function setMaxStepCount(int $maxStepCount): void
    {
        $this->maxStepCount = $maxStepCount;
    }

    /**
     * Set the saucer left direction movement.
     *
     * @param boolean $moveLeft
     * @return void
     */
    public function setMoveLeft(bool $moveLeft = true): void
    {
        $this->moveLeft = $moveLeft;
    }

    /**
     * Set the saucer right direction movement.
     *
     * @param boolean $moveRight
     * @return void
     */
    public function setMoveRight(bool $moveRight = true): void
    {
        $this->setMoveLeft(!$moveRight);
    }
}
