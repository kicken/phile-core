<?php

namespace Phile\Exception;

class PluginNotFoundException extends PluginException {
    public function __construct(string $class){
        parent::__construct(sprintf("Plugin '%s' not found.", $class));
    }
}
