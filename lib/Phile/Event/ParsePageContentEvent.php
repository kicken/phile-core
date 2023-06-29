<?php

namespace Phile\Event;

use Phile\Model\Page;
use Symfony\Contracts\EventDispatcher\Event;

class ParsePageContentEvent extends Event {
    const BEFORE = 'parse_page_content_before';
    const AFTER = 'parse_page_content_after';

    private $page;
    private $content;
    private $parsedContent;

    public function __construct(Page $page, string $content){
        $this->page = $page;
        $this->content = $content;
    }

    public function getPage() : Page{
        return $this->page;
    }

    public function getContent() : string{
        return $this->content;
    }

    public function setContent(string $content){
        $this->content = $content;
    }

    public function getParsedContent() : string{
        return $this->parsedContent;
    }

    public function setParsedContent($parsedContent) : void{
        $this->parsedContent = $parsedContent;
    }
}
