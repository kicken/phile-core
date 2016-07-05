<?php
/**
 * Plugin class
 */
namespace Phile\Plugin\ErrorHandler;

use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;
use Phile\Plugin\ErrorHandler\Handler\Development;
use Phile\Plugin\ErrorHandler\Handler\ErrorLog;

/**
 * Class Plugin
 * Default Phile parser plugin for Markdown
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\ParserMarkdown
 */
class ErrorHandlerPlugin extends AbstractPlugin
{
    const HANDLER_ERROR_LOG      = 'error_log';
    const HANDLER_DEVELOPMENT    = 'development';

    protected $events = ['plugins_loaded' => 'onPluginsLoaded'];

    /**
     * called on 'plugins_loaded' event
     *
     * @param  null $data
     * @throws \Phile\Exception\ServiceLocatorException
     */
    public function onPluginsLoaded($data = null)
    {
        switch ($this->settings['handler']) {
            case ErrorHandlerPlugin::HANDLER_ERROR_LOG:
                ServiceLocator::registerService(
                    'Phile_ErrorHandler',
                    new ErrorLog($this->settings)
                );
                break;
            case ErrorHandlerPlugin::HANDLER_DEVELOPMENT:
                ServiceLocator::registerService(
                    'Phile_ErrorHandler',
                    new Development($this->settings)
                );
                break;
        }
    }
}
