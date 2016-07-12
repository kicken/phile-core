<?php
/**
 * The page model
 */
namespace Phile\Model;

use Phile\Event\LoadPageContentEvent;
use Phile\Event\ParsePageContentEvent;
use Phile\Event\ParsePageMetaEvent;
use Phile\ServiceLocator\MetaParserInterface;
use Phile\ServiceLocator\ParserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * the Model class for a page
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Model
 */
class Page
{
    /** @var array Phile global settings */
    private $settings;
    /** @var EventDispatcherInterface Event dispatcher */
    private $dispatcher;
    /** @var string Content file path */
    private $contentFile;
    /** @var ParserInterface Content parser to use */
    private $parser;
    /** @var MetaParserInterface Meta parser to use  */
    private $metaParser;

    /** @var string Loaded file content cache */
    private $content;
    /** @var string Parsed page content cache */
    private $parsedContent;
    /** @var array Page meta data cache*/
    private $meta;

    public function __construct(array $settings, EventDispatcherInterface $dispatcher, ParserInterface $parser, MetaParserInterface $metaParser, $contentFile)
    {
        $this->settings = $settings;
        $this->dispatcher = $dispatcher;
        $this->contentFile = $contentFile;
        $this->parser = $parser;
        $this->metaParser = $metaParser;
    }

    public function getContent()
    {
        if (!$this->content) {
            $event = new LoadPageContentEvent($this);
            $this->dispatcher->dispatch(LoadPageContentEvent::BEFORE, $event);

            $content = $event->getContent();
            if ($content === null) {
                $content = file_get_contents($this->contentFile);
                $event->setContent($content);
            }

            $this->dispatcher->dispatch(LoadPageContentEvent::AFTER, $event);
            $this->content = $event->getContent();
        }

        return $this->content;
    }

    public function getParsedContent(){
        if (!$this->parsedContent){
            $content = $this->getContent();

            $event = new ParsePageContentEvent($this, $content);
            $this->dispatcher->dispatch(ParsePageContentEvent::BEFORE, $event);

            $content = $event->getContent();
            $parsedContent = $this->parser->parse($content);
            $event->setParsedContent($parsedContent);

            $this->dispatcher->dispatch(ParsePageContentEvent::AFTER, $event);
            $this->parsedContent = $event->getParsedContent();
        }

        return $this->parsedContent;
    }

    public function getMeta()
    {
        if (!$this->meta){
            $content = $this->getContent();

            $event = new ParsePageMetaEvent($this, $content);
            $this->dispatcher->dispatch(ParsePageMetaEvent::BEFORE, $event);

            $content = $event->getContent();
            $meta = $this->metaParser->parse($content);
            $event->setMeta($meta);

            $this->dispatcher->dispatch(ParsePageMetaEvent::AFTER, $event);
            $this->meta = new Meta($event->getMeta());
        }

        return $this->meta;
    }

    /**
     * get the title of page from meta information
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->meta['title'];
    }

    public function getContentFile(){
        return $this->contentFile;
    }
}
