<?php

namespace Phile\Event;


use Phile\Model\Page;
use Symfony\Component\EventDispatcher\Event;

class ParsePageMetaEvent extends Event {
    const BEFORE = 'parse_page_meta_before';
    const AFTER = 'parse_page_meta_after';

    /** @var Page */
    private $page;
    /** @var string */
    private $content;
    /** @var array */
    private $meta = [];

    public function __construct(Page $page, string $content){
        $this->page = $page;
        $this->content = $content;
    }

    public function getPage() : Page{
        return $this->page;
    }

    public function getMeta() : array{
        return $this->meta;
    }

    public function setMeta($meta):void{
        $this->meta = $meta;
    }

    public function getContent() : string{
        return $this->content;
    }

    public function setContent($content):void{
        $this->content = $content;
    }
}
