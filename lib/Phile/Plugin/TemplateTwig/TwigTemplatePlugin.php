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
    protected $events = ['plugins_loaded' => 'onPluginsLoaded'];

    /**
     * onPluginsLoaded method
     *
     * @param null $data
     *
     * @return mixed|void
     */
    public function onPluginsLoaded($data = null)
    {
        ServiceLocator::registerService(
            'Phile_Template',
            new Twig($this->settings)
        );
    }
}
