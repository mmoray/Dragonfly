<?php

namespace DragonFly\Manager;

use DragonFly\Base\Singleton;
use DragonFly\Event\KeyboardEvent;

class InputManager extends Manager
{
    /**
     * Keyboard event to send to all game objects.
     *
     * @var KeyboardEvent
     */
    private $keyEvent;

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
        $this->keyEvent = new KeyboardEvent;
        $this->setType(self::TYPE_INPUT);
    }

    /**
     * Destroy class and properties.
     */
    public function __destruct()
    {
        unset($this->keyEvent);
    }

    /**
     * Convert the InputManager into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $input = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $input .= "\"keyEvent\":{$this->keyEvent}";
        $input .= "}";
        return $input;
    }

    /**
     * Get the input from the user.
     *
     * @return void
     */
    public function getInput(): void
    {
        stream_set_blocking(STDIN, false);
        $code = fgetc(STDIN);
        if ($code !== false) {
            $this->keyEvent->setValue(ord($code));
            $this->dispatch($this->keyEvent);
        }
    }

    /**
     * Return the one and only instance of the class.
     *
     * @return Singleton
     */
    public static function getInstance(): Singleton
    {
        if (!(self::$instance instanceof InputManager)) {
            self::$instance = new InputManager;
        }
        return self::$instance;
    }
    
    /**
     * Shutdown InputManager.
     *
     * @return void
     */
    public function shutDown(): void
    {
        if ($this->isStarted()) {
            parent::shutDown();
            system('stty sane');
            stream_set_blocking(STDIN, true);
        }
    }

    /**
     * Startup InputManager.
     *
     * @return void
     * @throws Exception DisplayManager has not been started.
     */
    public function startUp(): void
    {
        if (!DisplayManager::getInstance()->isStarted()) {
            throw new Exception("Can not start the {$this->getType()}");
        }
        parent::startUp();
        system('stty cbreak -echo');
    }
}
