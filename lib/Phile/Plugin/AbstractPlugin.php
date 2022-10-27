<?php
/**
 * Plugin class
 */

namespace Phile\Plugin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * the AbstractPlugin class for implementing a plugin for PhileCMS
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin
 */
abstract class AbstractPlugin implements EventSubscriberInterface {
    /** @var array Plugin specific configuration */
    protected $config;
    /** @var array Phile's global configuration */
    protected $phileConfig;

    /**
     * AbstractPlugin constructor.
     *
     * @param array $pluginConfig Plugin specific configuration
     * @param array $phileConfig Global Phile configuration
     */
    final public function __construct($pluginConfig, $phileConfig){
        $this->config = $pluginConfig;
        $this->phileConfig = $phileConfig;
        $this->initialize();
    }

    public static function getSubscribedEvents(){
        return [];
    }

    public function initialize(){
    }

    /**
     * Get a path relative to the plugin's source directory.
     *
     * @param string $sub
     *
     * @return string
     */
    public function getPluginPath($sub = ''){
        static $dir = null;
        if ($dir === null){
            $rf = new \ReflectionObject($this);
            $dir = dirname($rf->getFileName()) . DIRECTORY_SEPARATOR;
        }

        return $dir . ltrim($sub, DIRECTORY_SEPARATOR);
    }
}
