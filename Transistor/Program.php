<?php

namespace Transistor;

require_once sprintf('%s/../src/DragonFly/Program/Game.php', __DIR__);

use DragonFly\Manager\DisplayManager;
use DragonFly\Manager\GameManager;
use DragonFly\Manager\WorldManager;
use DragonFly\Manager\ResourceManager;
use DragonFly\Program\Game;
use DragonFly\World\Vector;
use Exception;
use Transistor\Objects\Breadboard;
use Transistor\Objects\Transistor;
use Transistor\Objects\Pin;

class Program extends Game
{
    public function __construct()
    {
        parent::__construct();
        require_once sprintf('%s/vendor/autoload.php', __DIR__);
    }

    public function run(): void
    {
        try {
            // Startup game engine.
            $gm = GameManager::getInstance();
            $gm->startUp();

            // Set the world view.
            $wm = WorldManager::getInstance();
            $wm->getBoundary()->setHorizontal(DisplayManager::getInstance()->getHorizontalPixels());
            $wm->getBoundary()->setVertical(DisplayManager::getInstance()->getVerticalPixels());
            $wm->getView()->setCorner(new Vector(($wm->getBoundary()->getHorizontal() - $wm->getView()->getHorizontal())/ 2, ($wm->getBoundary()->getVertical() - $wm->getView()->getVertical()) / 2));
            $wm->getView()->setHorizontal(DisplayManager::getInstance()->getHorizontal());
            $wm->getView()->setVertical(DisplayManager::getInstance()->getVertical());

            // Load game resources.
            $rm = ResourceManager::getInstance();
            $rm->loadSprite(Pin::SPRITE_PATH, Pin::OBJECT_PIN);
            $rm->loadSprite(Transistor::SPRITE_PATH, Transistor::OBJECT_TANSISTOR);
            
            // Create game objects.
            new Breadboard;
            
            // Run game.
            $gm->run();
            $gm->shutDown();
        }
        catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}

(new Program)->run();
