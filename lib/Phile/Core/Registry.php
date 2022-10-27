<?php

namespace Phile\Core;

/**
 * the Registry class for implementing a registry
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Core
 */
class Registry extends \ArrayObject {
    /**
     * Registry object provides storage for shared objects.
     *
     * @var Registry
     */
    private static $registry = null;

    private function __construct(){
        parent::__construct();
    }

    /**
     * Retrieves the default registry instance.
     *
     * @return Registry
     */
    public static function getInstance() : Registry{
        if (self::$registry === null){
            self::$registry = new self;
        }

        return self::$registry;
    }

    /**
     * getter method, basically same as offsetGet().
     *
     * This method can be called from an object of type Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index - get the value associated with $index
     *
     * @return mixed
     * @throws \Exception if no entry is registered for $index.
     */
    public static function get(string $index){
        $instance = self::getInstance();
        if (!$instance->offsetExists($index)){
            throw new \RuntimeException("No entry is registered for key '$index'", 1398536594);
        }

        return $instance->offsetGet($index);
    }

    /**
     * setter method, basically same as offsetSet().
     *
     * This method can be called from an object of type Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index The location in the ArrayObject in which to store
     *                      the value.
     * @param mixed $value The object to store in the ArrayObject.
     *
     * @return void
     */
    public static function set(string $index, $value){
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    /**
     * Returns TRUE if the $index is a named value in the registry,
     * or FALSE if $index was not found in the registry.
     *
     * @param string $index
     *
     * @return boolean
     */
    public static function isRegistered(string $index) : bool{
        if (self::$registry === null){
            return false;
        }

        return self::$registry->offsetExists($index);
    }
}
