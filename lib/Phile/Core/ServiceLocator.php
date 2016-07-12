<?php
/**
 * The ServiceLocator class
 */
namespace Phile\Core;

use Phile\Exception\ServiceLocatorException;
use Phile\ServiceLocator\CacheInterface;
use Phile\ServiceLocator\ErrorHandlerInterface;
use Phile\ServiceLocator\MetaParserInterface;
use Phile\ServiceLocator\ParserInterface;
use Phile\ServiceLocator\PersistenceInterface;
use Phile\ServiceLocator\TemplateInterface;

/**
 * the Service Locator class
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Core
 */
class ServiceLocator
{
    /**
     * @var array of services
     */
    protected static $services;

    /**
     * @var array $serviceMap for mapping speaking names/keys to the interfaces
     */
    protected static $serviceMap = array(
    'Phile_Cache'            => CacheInterface::class,
    'Phile_Template'         => TemplateInterface::class,
    'Phile_Parser'           => ParserInterface::class,
    'Phile_Data_Persistence' => PersistenceInterface::class,
    'Phile_Parser_Meta'      => MetaParserInterface::class,
    'Phile_ErrorHandler'     => ErrorHandlerInterface::class,
    );

    /**
     * method to register a service
     *
     * @param string $serviceKey the key for the service
     * @param mixed  $object
     *
     * @throws ServiceLocatorException
     */
    public static function registerService($serviceKey, $object)
    {
        $interface  = self::$serviceMap[$serviceKey];
        if (!($object instanceof $interface)) {
            throw new ServiceLocatorException("the object must implement the interface: '{$interface}'", 1398536617);
        }
        self::$services[$serviceKey] = $object;
    }

    /**
     * checks if a service is registered
     *
     * @param string $serviceKey
     *
     * @return bool
     */
    public static function hasService($serviceKey)
    {
        return (isset(self::$services[$serviceKey]));
    }

    /**
     * returns a service
     *
     * @param string $serviceKey the service key
     *
     * @return mixed
     * @throws ServiceLocatorException
     */
    public static function getService($serviceKey)
    {
        if (!isset(self::$services[$serviceKey])) {
            throw new ServiceLocatorException("the service '{$serviceKey}' is not registered", 1398536637);
        }

        return self::$services[$serviceKey];
    }
}
