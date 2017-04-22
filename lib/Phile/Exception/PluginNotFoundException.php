<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/11/2016
 * Time: 7:40 PM
 */

namespace Phile\Exception;

class PluginNotFoundException extends PluginException
{
    public function __construct($class)
    {
        parent::__construct(sprintf("Plugin '%s' not found.", $class));
    }
}
