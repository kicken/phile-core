<?php
/**
 * Plugin class
 */
namespace Phile\Plugin\ParserMeta;

use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;
use Phile\Plugin\ParserMeta\Parser\MetaParser;

/**
 * Class Plugin
 * Default Phile parser plugin for Markdown
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\ParserMeta
 */
class MetaParserPlugin extends AbstractPlugin
{
    public function initialize()
    {
        ServiceLocator::registerService(
            'Phile_Parser_Meta',
            new MetaParser($this->config)
        );
    }
}
