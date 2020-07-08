<?php

namespace DragonFly\Base;

abstract class Singleton
{
    /**
     * Instantiate class and properties.
     */
    abstract protected function __construct();

    /**
     * Return the one and only instance of the class.
     *
     * @return Singleton
     */
    abstract public static function getInstance(): Singleton;
}
