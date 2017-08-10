<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/11/2016
 * Time: 9:24 PM
 */

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

    public function __construct(Page $page, $content){
        $this->page = $page;
        $this->content = $content;
    }

    public function getPage(){
        return $this->page;
    }

    public function getMeta(){
        return $this->meta;
    }

    public function setMeta($meta){
        $this->meta = $meta;
    }

    public function getContent(){
        return $this->content;
    }

    public function setContent($content){
        $this->content = $content;
    }
}
