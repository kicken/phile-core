<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/11/2016
 * Time: 8:11 PM
 */

namespace Phile\Event;


use Symfony\Component\EventDispatcher\Event;

class RoutingEvent extends Event {
    const BEFORE = 'route_before';
    const AFTER = 'route_after';

    private $requestUrl;
    private $contentPath;

    public function __construct($requestUrl){
        $this->requestUrl = $requestUrl;
    }

    public function getRequestUrl(){
        return $this->requestUrl;
    }

    public function setRequestUrl($requestUrl){
        $this->requestUrl = $requestUrl;
    }

    public function getContentPath(){
        return $this->contentPath;
    }

    public function setContentPath($contentPath){
        $this->contentPath = $contentPath;
    }
}
