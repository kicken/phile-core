<?php
/**
 * Plugin class
 */

namespace Phile\Plugin;

use Phile\Core;

abstract class AbstractPlugin {
    /** @var Core */
    protected $core;
    /** @var array Plugin specific configuration */
    protected $config;

    final public function __construct(Core $core, array $config){
        $this->core = $core;
        $this->config = $config;
        $this->initialize();
    }

    public function initialize() : void{
    }
}
