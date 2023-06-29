<?php

namespace Phile\Event;

use Phile\Model\Page;
use Symfony\Contracts\EventDispatcher\Event;

class LoadPageContentEvent extends Event {
    const BEFORE = 'load_page_content_before';
    const AFTER = 'load_page_content_after';

    private $page;
    private $content;

    public function __construct(Page $page){
        $this->page = $page;
    }

    public function getPage() : Page{
        return $this->page;
    }

    public function getContent() : ?string{
        return $this->content;
    }

    public function setContent(string $content) : void{
        $this->content = $content;
    }
}
