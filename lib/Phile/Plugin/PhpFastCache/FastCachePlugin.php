<?php
/**
 * Plugin class
 */
namespace Phile\Plugin\PhpFastCache;

use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;

/**
 * Class Plugin
 * Default Phile cache engine
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\PhpFastCache
 */
class FastCachePlugin extends AbstractPlugin
{
    public function initialize()
    {
        // phpFastCache not working in CLI mode...
        if (php_sapi_name() === 'cli') {
            return;
        }
        
        $config = $this->config + \phpFastCache::$config;
        $driver = $this->config['driver'];
        $cache = phpFastCache($driver, $config);
        ServiceLocator::registerService('Phile_Cache', new PhpFastCache($cache));
    }
}
