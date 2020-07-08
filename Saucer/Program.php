<?php

namespace Saucer;

require_once sprintf('%s/../src/DragonFly/Program/Game.php', __DIR__);

use DragonFly\Manager\LogManager;

use DragonFly\Manager\GameManager;
use DragonFly\Manager\WorldManager;
use DragonFly\Manager\ResourceManager;
use DragonFly\Program\Game;
use Exception;
use Saucer\Objects\Bullet;
use Saucer\Objects\Explosion;
use Saucer\Objects\Hero;
use Saucer\Objects\NukeView;
use Saucer\Objects\PointView;
use Saucer\Objects\Saucer;
use Saucer\Objects\Star;

class Program extends Game
{
    public function __construct()
    {
        parent::__construct();
        require_once sprintf('%s/vendor/autoload.php', __DIR__);
    }

    public function run(): void
    {
        $gm = GameManager::getInstance();
        $gm->startUp();
        try {
            
            // Set the world view.
            $wm = WorldManager::getInstance();
            $wm->getView()->setHorizontal($wm->getBoundary()->getHorizontal());
            $wm->getView()->setVertical($wm->getBoundary()->getVertical());

            // Load game resources.
            $rm = ResourceManager::getInstance();
            $rm->loadSprite(Bullet::SPRITE_PATH, Bullet::OBJECT_BULLET);
            $rm->loadSprite(Explosion::SPRITE_PATH, Explosion::OBJECT_EXPLOSION);
            $rm->loadSprite(Hero::SPRITE_PATH, Hero::OBJECT_HERO);
            $rm->loadSprite(Saucer::SPRITE_PATH, Saucer::OBJECT_SAUCER);

            
            // Create game objects.
            new NukeView;
            new PointView;
            new Hero;
            for ($i = 0; $i < 16; $i++) {
                new Saucer;
                new Star;
            }

            
            // Run game.
            $gm->run();
            $gm->shutDown();
        }
        catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        //unset($gm);

        /*
        $lm = LogManager::getInstance();
        $lm->startUp();
        $lm->error(new Exception('This is a test'));
        $lm->warning(new Exception('This is a test 2'));
        $lm->shutDown();
        unset($lm);
        */
    }
}

(new Program)->run();
