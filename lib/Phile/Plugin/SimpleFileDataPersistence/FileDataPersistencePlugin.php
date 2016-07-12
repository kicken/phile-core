<?php
/**
 * Plugin class
 */
namespace Phile\Plugin\SimpleFileDataPersistence;

use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;
use Phile\Plugin\SimpleFileDataPersistence\Persistence\SimpleFileDataPersistence;

/**
 * Class Plugin
 * Default Phile data persistence engine
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\SimpleFileDataPersistence
 */
class FileDataPersistencePlugin extends AbstractPlugin
{
    public function initialize()
    {
        ServiceLocator::registerService(
            'Phile_Data_Persistence',
            new SimpleFileDataPersistence($this->config['storage_dir'])
        );
    }
}
