<?php
/**
 * Plugin class
 */

namespace Phile\Plugin\ParserMarkdown;

use Michelf\MarkdownExtra;
use Phile\Plugin\AbstractPlugin;
use Phile\Service\ParserInterface;

/**
 * Class Plugin
 * Default Phile parser plugin for Markdown
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\ParserMarkdown
 */
class MarkdownPlugin extends AbstractPlugin implements ParserInterface {
    public function initialize() : void{
        $this->core->registerService(ParserInterface::class, $this);
    }

    /**
     * overload parse with the MarkdownExtra parser
     *
     * @param string $data
     *
     * @return string
     */
    public function parse(string $data) : string{
        $parser = new MarkdownExtra;
        foreach ($this->config as $key => $value){
            if (property_exists($parser, $key)){
                $parser->{$key} = $value;
            }
        }

        return $parser->transform($data);
    }
}
