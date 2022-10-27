<?php

namespace Phile\Event;


use Symfony\Component\EventDispatcher\Event;

class CoreEvent extends Event {
    const LOADED = 'core_loaded';
}
