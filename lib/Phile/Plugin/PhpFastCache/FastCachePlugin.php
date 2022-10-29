<?php
/**
 * Plugin class
 */

namespace Phile\Plugin\PhpFastCache;

use Phile\Core\ServiceRegistry;
use Phile\Plugin\AbstractPlugin;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;
use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidTypeException;
use Phpfastcache\Exceptions\PhpfastcacheLogicException;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\SimpleCache\CacheInterface;

/**
 * Class Plugin
 * Default Phile cache engine
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\PhpFastCache
 */
class FastCachePlugin extends AbstractPlugin {
    /**
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheInvalidTypeException
     * @throws PhpfastcacheLogicException
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheInvalidConfigurationException
     */
    public function initialize() : void{
        // phpFastCache not working in CLI mode...
        if (php_sapi_name() === 'cli'){
            return;
        }

        $config = $this->config + CacheManager::getDefaultConfig()->toArray();
        $driver = $this->config['driver'];
        unset($config['active'], $config['driver']);
        $engine = new Psr16Adapter($driver, new ConfigurationOption($config));

        $this->core->registerService(CacheInterface::class, $engine);
    }
}
