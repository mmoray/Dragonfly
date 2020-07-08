<?php

namespace DragonFlyEgg;

require_once sprintf('%s/../src/DragonFly/Program/Game.php', __DIR__);

use DragonFly\Manager\GameManager;
use DragonFly\Program\Game;
use DragonFlyEgg\Objects\Saucer;
use Exception;

class Chapter4 extends Game
{
    public function __construct()
    {
        parent::__construct();
        require_once sprintf('%s/vendor/autoload.php', __DIR__);
    }

    public function run(): void
    {
        $gm = GameManager::getInstance();
        if ($gm->startUp()) {
            try {
                new Saucer(10, 10, 2, 0);
                //new Saucer(20, 15, 3, 25);
                //new Saucer(25, 20, 2, 50);
                //new Saucer(30, 25, 3, 75);
                $gm->run();
            }
            catch (Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
            $gm->shutDown();
        }
    }
}

(new Chapter4)->run();
