<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/11/2016
 * Time: 7:59 PM
 */

namespace Phile\Exception;

class PluginInitializationException extends PluginException {
    /**
     * PluginInitializationException constructor.
     *
     * @param string $class
     * @param \Exception $previous
     */
    public function __construct($class, \Exception $previous = null){
        parent::__construct(sprintf("Plugin '%s' could not be initialized.", $class), 0, $previous);
    }
}
