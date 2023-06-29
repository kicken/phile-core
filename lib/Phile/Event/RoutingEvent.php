<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/11/2016
 * Time: 8:11 PM
 */

namespace Phile\Event;

use Symfony\Contracts\EventDispatcher\Event;

class RoutingEvent extends Event {
    const BEFORE = 'route_before';
    const AFTER = 'route_after';

    private $requestUrl;
    private $contentPath;

    public function __construct(string $requestUrl){
        $this->requestUrl = $requestUrl;
    }

    public function getRequestUrl() : string{
        return $this->requestUrl;
    }

    public function setRequestUrl(string $requestUrl) : void{
        $this->requestUrl = $requestUrl;
    }

    public function getContentPath() : ?string{
        return $this->contentPath;
    }

    public function setContentPath(?string $contentPath) : void{
        $this->contentPath = $contentPath;
    }
}
