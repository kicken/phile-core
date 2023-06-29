<?php

namespace Phile\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CoreEvent extends Event {
    const LOADED = 'core_loaded';
}
