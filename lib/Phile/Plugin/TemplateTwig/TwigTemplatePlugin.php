<?php
/**
 * Plugin class
 */
namespace Phile\Plugin\TemplateTwig;

use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;
use Phile\Plugin\TemplateTwig\Template\Twig;

/**
 * Class Plugin
 * Default Phile template engine
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\TemplateTwig
 */
class TwigTemplatePlugin extends AbstractPlugin
{
    public function initialize()
    {
        ServiceLocator::registerService(
            'Phile_Template',
            new Twig($this->config, $this->phileConfig)
        );
    }
}
