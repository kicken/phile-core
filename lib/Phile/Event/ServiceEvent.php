<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 4/22/2017
 * Time: 3:46 AM
 */

namespace Phile\Event;


use Symfony\Component\EventDispatcher\Event;

class ServiceEvent extends Event {
    const REGISTERED = 'service_registered';

    private $name;
    private $service;

    public function __construct($name, $service){
        $this->name = $name;
        $this->service = $service;
    }

    public function getName(){
        return $this->name;
    }

    public function getService(){
        return $this->service;
    }

    public function setService($service){
        $this->service = $service;
    }
}
