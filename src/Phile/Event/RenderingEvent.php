<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/11/2016
 * Time: 8:53 PM
 */

namespace Phile\Event;


use Phile\Model\Page;
use Phile\ServiceLocator\TemplateInterface;
use Symfony\Component\EventDispatcher\Event;

class RenderingEvent extends Event {
    const BEFORE = 'rendering_before';
    const AFTER = 'rendering_after';

    private $page;
    private $template;
    private $content;

    public function __construct(Page $page, TemplateInterface $template, $content){
        $this->page = $page;
        $this->template = $template;
        $this->content = $content;
    }

    public function getPage(){
        return $this->page;
    }

    public function getTemplate(){
        return $this->template;
    }

    public function getContent(){
        return $this->content;
    }

    public function setContent($content){
        $this->content = $content;
    }
}
