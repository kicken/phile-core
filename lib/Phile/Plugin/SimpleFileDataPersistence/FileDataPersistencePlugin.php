<?php
/**
 * Plugin class
 */

namespace Phile\Plugin\SimpleFileDataPersistence;

use Phile\Plugin\AbstractPlugin;
use Phile\Service\PersistenceInterface;

/**
 * Class Plugin
 * Default Phile data persistence engine
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\SimpleFileDataPersistence
 */
class FileDataPersistencePlugin extends AbstractPlugin implements PersistenceInterface {
    public function initialize() : void{
        $this->core->registerService(PersistenceInterface::class, $this);
    }

    public function has(string $key) : bool{
        return (file_exists($this->getStorageFile($key)));
    }

    public function get(string $key){
        if (!$this->has($key)){
            throw new \RuntimeException("no data storage for key '{$key}' exists!");
        }

        return unserialize(file_get_contents($this->getStorageFile($key)));
    }

    public function set(string $key, $value) : void{
        file_put_contents($this->getStorageFile($key), serialize($value));
    }

    public function delete(string $key, array $options = []) : void{
        if (!$this->has($key)){
            throw new \RuntimeException("no data storage for key '{$key}' exists!");
        }
        unlink($this->getStorageFile($key));
    }

    protected function getInternalKey($key) : string{
        return md5($key);
    }

    protected function getStorageFile($key) : string{
        return $this->config['storage_dir'] . $this->getInternalKey($key) . '.ds';
    }
}
