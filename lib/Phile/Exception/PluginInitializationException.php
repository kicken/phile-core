<?php

namespace Phile\Exception;

class PluginInitializationException extends PluginException {
    /**
     * PluginInitializationException constructor.
     *
     * @param string $class
     * @param ?\Exception $previous
     */
    public function __construct(string $class, \Exception $previous = null){
        parent::__construct(sprintf("Plugin '%s' could not be initialized.", $class), 0, $previous);
    }
}
