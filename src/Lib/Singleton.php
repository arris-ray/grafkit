<?php

namespace Grafkit\Lib;

trait Singleton
{
    /**
     * @var array
     */
    private static array $_instance = [];

    /**
     * @return mixed
     */
    final public static function getInstance(): mixed
    {
        $calledClass = get_called_class();
        if (!isset( static::$_instance[$calledClass])) {
            static::$_instance[ $calledClass ] = new $calledClass();
        }
        return static::$_instance[$calledClass];
    }

    /**
     * Constructor.
     *
     * @internal Private to prevent direct object creation.
     */
    private function __construct() {}

    /**
     * Create a shallow copy of an object.
     *
     * @internal Private to prevent direct object cloning.
     */
    private function __clone() {}
}