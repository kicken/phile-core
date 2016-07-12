<?php
/**
 * Plugin class
 */
namespace Phile\Plugin\PhpFastCache;

use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;
use Phile\ServiceLocator\CacheInterface;

/**
 * Class Plugin
 * Default Phile cache engine
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\PhpFastCache
 */
class FastCachePlugin extends AbstractPlugin implements CacheInterface
{
    /** @var \BasePhpFastCache */
    private $engine;
    
    public function initialize()
    {
        // phpFastCache not working in CLI mode...
        if (php_sapi_name() === 'cli') {
            return;
        }
        
        $config = $this->config + \phpFastCache::$config;
        $driver = $this->config['driver'];
        $this->engine = phpFastCache($driver, $config);
        
        ServiceLocator::registerService('Phile_Cache', $this);
    }

    public function has($key)
    {
        return ($this->engine->get($key) !== null);
    }

    public function get($key)
    {
        return $this->engine->get($key);
    }

    public function set($key, $value, $time = 300, array $options = array())
    {
        $this->engine->set($key, $value, $time, $options);
    }

    public function delete($key, array $options = array())
    {
        $this->engine->delete($key, $options);
    }

    public function clean()
    {
        $this->engine->clean();
    }
}
