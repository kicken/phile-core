<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 4/22/2017
 * Time: 4:47 AM
 */

namespace Phile\Event;


use Symfony\Component\EventDispatcher\Event;

class CoreEvent extends Event {
    const LOADED = 'core_loaded';

    public function __construct(){
    }
}
