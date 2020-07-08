<?php

require_once sprintf('%s/src/DragonFly/Program/Game.php', __DIR__);

use DragonFly\Manager\GameManager;
use DragonFly\Manager\LogManager;
use DragonFly\Manager\Manager;
use DragonFly\Program\Game;

class Program extends Game
{
    public function run(): void
    {
        //self::chapterOneTest();
        //self::chapterTwoTest();
        $this->inputTest();
    }

    /**
     * Chapter 1.
     *
     * @return void
     */
    private function chapterOneTest(): void
    {
        $l = LogManager::getInstance();
        $l->startUp();
        $l->info('l: %s', $l->getType());

        $m = Manager::getInstance();
        $l->info('m: %s', $m->getType());

        $m2 = Manager::getInstance();
        $l->info("m2: {$m2->getType()}");

        $m2->settype('Updated');
        $l->info("m2: {$m2->getType()}");
        $l->info('m: %s', $m->getType());
        $l->info('l: %s', $l->getType());
        
        $l->shutDown();
        
        unset($l);
        unset($m);
        unset($m2);
    }

    /**
     * Chapter 2.
     *
     * @return void
     */
    private function chapterTwoTest(): void
    {
        $gm = GameManager::getInstance();
        if ($gm->startUp()) {
            $gm->run(true);
        }
        $gm->shutDown();
        unset($gm);
    }

    private function inputTest(): void
    {
        $gm = GameManager::getInstance();
        if ($gm->startUp()) {
            $gm->run();
        }
        $gm->shutDown();
        unset($gm);
    }
}

(new Program)->run();