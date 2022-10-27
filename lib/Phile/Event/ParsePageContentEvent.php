<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/11/2016
 * Time: 9:09 PM
 */

namespace Phile\Event;


use Phile\Model\Page;
use Symfony\Component\EventDispatcher\Event;

class ParsePageContentEvent extends Event {
    const BEFORE = 'parse_page_content_before';
    const AFTER = 'parse_page_content_after';

    private $page;
    private $content;
    private $parsedContent;

    public function __construct(Page $page, $content){
        $this->page = $page;
        $this->content = $content;
    }

    public function getPage(){
        return $this->page;
    }

    public function getContent(){
        return $this->content;
    }

    public function setContent($content){
        $this->content = $content;
    }

    public function getParsedContent(){
        return $this->parsedContent;
    }

    public function setParsedContent($parsedContent){
        $this->parsedContent = $parsedContent;
    }
}
