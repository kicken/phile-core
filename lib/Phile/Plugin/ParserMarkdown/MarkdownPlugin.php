<?php
/**
 * Plugin class
 */
namespace Phile\Plugin\ParserMarkdown;

use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;
use Phile\Plugin\ParserMarkdown\Parser\Markdown;

/**
 * Class Plugin
 * Default Phile parser plugin for Markdown
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\ParserMarkdown
 */
class MarkdownPlugin extends AbstractPlugin
{
    public function initialize()
    {
        ServiceLocator::registerService('Phile_Parser', new Markdown($this->config));
    }
}
