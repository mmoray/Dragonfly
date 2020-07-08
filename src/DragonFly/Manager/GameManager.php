<?php

namespace DragonFly\Manager;

use DragonFly\Base\Clock;
use DragonFly\Base\Singleton;
use DragonFly\Event\StepEvent;
use DragonFly\Manager\LogManager;
use DragonFly\Manager\Manager;

class GameManager extends Manager
{
    const FRAME_TIME_DEFAULT = 40;

    /**
     * Target time per game loop (microseconds).
     *
     * @var float
     */
    private $frameTime;

    /**
     * Game loop should stop if true.
     *
     * @var boolean
     */
    private $gameOver;

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
        $this->frameTime = 0;
        $this->setGameOver();
        $this->setType(self::TYPE_GAME);
    }

    /**
     * Destrory the class and properties.
     */
    public function __destruct()
    {
        unset($this->frameTime);
        unset($this->gameOver);
        parent::__destruct();
    }

    /**
     * Convert the GameManager into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $manager = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $manager .= "\"frameTime\":{$this->frameTime},";
        $manager .= "\"gameOver\":{$this->gameOver}";
        $manager .= "}";
        return $manager;;
    }

    /**
     * Return the one and only instance of the class.
     *
     * @return Singleton
     */
    public static function getInstance(): Singleton
    {
        if (!(self::$instance instanceof GameManager)) {
            self::$instance = new GameManager;
        }
        return self::$instance;
    }
    
    /**
     * Run game loop.
     *
     * @param boolean $test
     * @return void
     */
    public function run(bool $test = false): void
    {
        $clock = new Clock;
        $displayManager = DisplayManager::getInstance();
        $inputManager = InputManager::getInstance();
        $step = new StepEvent(0);
        $worldManager = WorldManager::getInstance();
        $this->setGameOver(false);
        while (!$this->gameOver) {
            $clock->delta();

            // DO GAME WORK
            $inputManager->getInput();
            $this->dispatch($step);
            $step->setStepCount($step->getStepCount() + 1);
            $worldManager->update();

            // DRAW TO SCREEN AND DISPLAY
            $displayManager->clearScreen();
            $worldManager->draw();
            $displayManager->render();

            // RUN TEST IF SPECIFIED
            if ($test && $step->getStepCount() === 100) {
                $this->setGameOver();
            }

            $loopTime = $clock->split();
            $intendedSleepTime = self::FRAME_TIME_DEFAULT - (($loopTime - $this->frameTime) * 1000);
            $clock->delta();
            if ($intendedSleepTime < 0) {
                $intendedSleepTime = 1;
            }
            time_nanosleep(0, $intendedSleepTime * 1000000);

            $actualSleepTime = $clock->split();
            $this->frameTime = $actualSleepTime - ($intendedSleepTime / 1000);
            if ($this->frameTime < 0) {
                $this->frameTime = 0;
            }
        }
    }

    /**
     * Set the game over status to indicated value. 
     * If true (default), will stop game loop.
     *
     * @param boolean $gameOver
     * @return void
     */
    public function setGameOver(bool $gameOver = true): void
    {
        $this->gameOver = $gameOver;
    }

    /**
     * Shutdown GameManager.
     *
     * @return void
     */
    public function shutDown(): void
    {
        parent::shutDown();
        if (WorldManager::getInstance()->isStarted()) {
            WorldManager::getInstance()->shutDown();
        }
        if (ResourceManager::getInstance()->isStarted()) {
            ResourceManager::getInstance()->shutDown();
        }
        if (InputManager::getInstance()->isStarted()) {
            InputManager::getInstance()->shutDown();
        }
        if (DisplayManager::getInstance()->isStarted()) {
            DisplayManager::getInstance()->shutDown();
        }
        if (LogManager::getInstance()->isStarted()) {
            LogManager::getInstance()->shutDown();
        }
    }

    /**
     * Startup GameManager.
     *
     * @return void
     */
    public function startUp(): void
    {
        try {
            LogManager::getInstance()->startUp();
            WorldManager::getInstance()->startUp();
            DisplayManager::getInstance()->startUp();
            InputManager::getInstance()->startUp();
            ResourceManager::getInstance()->startUp();
        }
        catch (Exception $e) {
            $this->shutDown();
            LogManager::getInstance()->error($e);
        }
    }
}
