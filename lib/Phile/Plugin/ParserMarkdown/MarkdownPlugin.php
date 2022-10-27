<?php
/**
 * Plugin class
 */

namespace Phile\Plugin\ParserMarkdown;

use Michelf\MarkdownExtra;
use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;
use Phile\ServiceLocator\ParserInterface;

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
    public function initialize(){
        ServiceLocator::registerService('Phile_Parser', $this);
    }

    /**
     * overload parse with the MarkdownExtra parser
     *
     * @param $data
     *
     * @return string
     */
    public function parse($data){
        $parser = new MarkdownExtra;
        foreach ($this->config as $key => $value){
            $parser->{$key} = $value;
        }

        return $parser->transform($data);
    }
}
