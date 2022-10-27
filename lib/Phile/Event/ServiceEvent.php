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

    public function __construct(string $name, object $service){
        $this->name = $name;
        $this->service = $service;
    }

    public function getName() : string{
        return $this->name;
    }

    public function getService() : object{
        return $this->service;
    }

    public function setService(object $service) : void{
        $this->service = $service;
    }
}
