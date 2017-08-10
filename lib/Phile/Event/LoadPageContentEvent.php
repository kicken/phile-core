<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/11/2016
 * Time: 8:59 PM
 */

namespace Phile\Event;


use Phile\Model\Page;
use Symfony\Component\EventDispatcher\Event;

class LoadPageContentEvent extends Event {
    const BEFORE = 'load_page_content_before';
    const AFTER = 'load_page_content_after';

    private $page;
    private $content;

    public function __construct(Page $page){
        $this->page = $page;
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
}
