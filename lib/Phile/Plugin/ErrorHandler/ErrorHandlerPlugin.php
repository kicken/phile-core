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
class ErrorHandlerPlugin extends AbstractPlugin {
    const HANDLER_ERROR_LOG = 'error_log';
    const HANDLER_DEVELOPMENT = 'development';

    public function initialize(){
        $handler = null;
        switch ($this->config['handler']){
            case ErrorHandlerPlugin::HANDLER_ERROR_LOG:
                $handler = new ErrorLog();
                break;
            case ErrorHandlerPlugin::HANDLER_DEVELOPMENT:
                $handler = new Development($this->config, $this->phileConfig);
                break;
        }

        if ($handler){
            ServiceLocator::registerService('Phile_ErrorHandler', $handler);
        }
    }
}
