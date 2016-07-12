<?php
/**
 * Plugin class
 */
namespace Phile\Plugin\SimpleFileDataPersistence;

use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;
use Phile\ServiceLocator\PersistenceInterface;

/**
 * Class Plugin
 * Default Phile data persistence engine
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\SimpleFileDataPersistence
 */
class FileDataPersistencePlugin extends AbstractPlugin implements PersistenceInterface
{
    public function initialize()
    {
        ServiceLocator::registerService('Phile_Data_Persistence', $this);
    }

    public function has($key)
    {
        return (file_exists($this->getStorageFile($key)));
    }

    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \RuntimeException("no data storage for key '{$key}' exists!");
        }

        return unserialize(file_get_contents($this->getStorageFile($key)));
    }

    public function set($key, $value)
    {
        file_put_contents($this->getStorageFile($key), serialize($value));
    }

    public function delete($key, array $options = array())
    {
        if (!$this->has($key)) {
            throw new \RuntimeException("no data storage for key '{$key}' exists!");
        }
        unlink($this->getStorageFile($key));
    }

    protected function getInternalKey($key)
    {
        return md5($key);
    }

    protected function getStorageFile($key)
    {
        return $this->config['storage_dir'] . $this->getInternalKey($key) . '.ds';
    }
}
