<?php
/**
 * The page model
 */

namespace Phile\Model;

use Phile\Core;
use Phile\Event\LoadPageContentEvent;
use Phile\Event\ParsePageContentEvent;
use Phile\Event\ParsePageMetaEvent;
use Phile\Service\MetaParserInterface;
use Phile\Service\ParserInterface;
use Phile\Service\RouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * the Model class for a page
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Model
 */
class Page {
    private $core;
    private $contentFile;

    /** @var string Loaded file content cache */
    private $content;
    /** @var string Parsed page content cache */
    private $parsedContent;
    /** @var array Page meta data cache */
    private $meta;

    public function __construct(Core $core, string $contentFile){
        $this->core = $core;
        $this->contentFile = $contentFile;
    }

    public function getContent() : string{
        if (!$this->content){
            $event = new LoadPageContentEvent($this);
            $dispatcher = $this->core->getService(EventDispatcherInterface::class);
            $dispatcher->dispatch(LoadPageContentEvent::BEFORE, $event);

            $content = $event->getContent();
            if ($content === null){
                $content = file_get_contents($this->contentFile);
                $event->setContent($content);
            }

            $dispatcher->dispatch(LoadPageContentEvent::AFTER, $event);
            $this->content = $event->getContent();
        }

        return $this->content;
    }

    public function getParsedContent() : string{
        if (!$this->parsedContent){
            $content = $this->getContent();
            $dispatcher = $this->core->getService(EventDispatcherInterface::class);

            $event = new ParsePageContentEvent($this, $content);
            $dispatcher->dispatch(ParsePageContentEvent::BEFORE, $event);

            $content = $event->getContent();
            $parsedContent = $this->core->getService(ParserInterface::class)->parse($content);
            $event->setParsedContent($parsedContent);

            $dispatcher->dispatch(ParsePageContentEvent::AFTER, $event);
            $this->parsedContent = $event->getParsedContent();
        }

        return $this->parsedContent;
    }

    public function getMeta(){
        if (!$this->meta){
            $content = $this->getContent();
            $dispatcher = $this->core->getService(EventDispatcherInterface::class);

            $event = new ParsePageMetaEvent($this, $content);
            $dispatcher->dispatch(ParsePageMetaEvent::BEFORE, $event);

            $content = $event->getContent();
            $meta = $this->core->getService(MetaParserInterface::class)->parse($content);
            $event->setMeta($meta);

            $dispatcher->dispatch(ParsePageMetaEvent::AFTER, $event);
            $this->meta = new Meta($event->getMeta());
        }

        return $this->meta;
    }

    public function getTitle() : ?string{
        return $this->getMeta()['title'];
    }

    public function getContentFile() : string{
        return $this->contentFile;
    }

    public function getUrl() : string{
        /** @var RouterInterface $router */
        $router = $this->core->getService(RouterInterface::class);

        return $router->urlForPath($this->contentFile);
    }
}
