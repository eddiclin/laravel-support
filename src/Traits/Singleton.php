<?php

namespace Eddic\Support\Traits;

trait Singleton
{
    private static $uniqueInstance = null;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (null === static::$uniqueInstance) {
            static::$uniqueInstance = new static;
        }

        return static::$uniqueInstance;
    }
}
