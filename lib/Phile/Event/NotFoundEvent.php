<?php

namespace Phile\Event;

use Symfony\Contracts\EventDispatcher\Event;

class NotFoundEvent extends Event {
    const AFTER = 'after_not_found';

    private $requestUrl;

    public function __construct(string $requestUrl){
        $this->requestUrl = $requestUrl;
    }

    public function getRequestUrl() : string{
        return $this->requestUrl;
    }
}
