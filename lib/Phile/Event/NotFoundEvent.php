<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/11/2016
 * Time: 8:50 PM
 */

namespace Phile\Event;


use Symfony\Component\EventDispatcher\Event;

class NotFoundEvent extends Event {
    const AFTER = 'after_not_found';

    private $requestUrl;

    public function __construct($requestUrl){
        $this->requestUrl;
    }

    public function getRequestUrl(){
        return $this->requestUrl;
    }
}
