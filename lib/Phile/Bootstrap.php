<?php
/**
 * the Bootstrap of Phile
 */
namespace Phile;

use Phile\Core\Router;
use Phile\Exception\PluginException;
use Phile\Plugin\PluginRepository;

/**
 * Phile
 *
 * @author  Frank Nägler
 * @link    https://github.com/PhileCMS/Phile
 * @license http://opensource.org/licenses/MIT
 * @version 0.1
 */
class Bootstrap
{
    /**
     * @var \Phile\Bootstrap instance of Bootstrap class
     */
    static protected $instance = null;

    /**
     * @var array the settings array
     */
    protected $settings;

    /**
     * @var array the loaded plugins
     */
    protected $plugins;

    /**
     * the constructor
     * Disable direct creation of this object.
     */
    protected function __construct()
    {
    }

    /**
     * Disable direct cloning of this object.
     */
    protected function __clone()
    {
    }

    /**
     * Return instance of Bootstrap class as singleton
     *
     * @return Bootstrap
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            self::$instance = new Bootstrap();
        }
        return static::$instance;
    }

    /**
     * initialize basics
     * @param array $configuration
     * @return $this
     * @throws PluginException
     */
    public function initializeBasics(array $configuration=[])
    {
        $this->initializeDefinitions();
        $this->initializeConfiguration($configuration);
        $this->initializeFilesAndFolders();
        $this->initializePlugins();
        return $this;
    }

    /**
     * initialize the global definitions
     */
    protected function initializeDefinitions()
    {
        // for php unit testings, we need to check if constant is defined
        // before setting them, because there is a bug in PHPUnit which
        // init our bootstrap multiple times.
        defined('PHILE_VERSION') || define('PHILE_VERSION', '1.7.1');
        defined('PHILE_CLI_MODE') || define('PHILE_CLI_MODE', (php_sapi_name() === 'cli'));
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('ROOT_DIR') || define('ROOT_DIR', realpath(__DIR__ . DS . '..' . DS . '..' . DS) . DS);
    }

    /**
     * initialize configuration
     * @param array $configuration
     */
    protected function initializeConfiguration(array $configuration)
    {
        $defaults = [
            'base_url' => (new Router)->getBaseUrl()
            , 'site_title' => 'PhileCMS'
            , 'theme' => 'default'
            , 'date_format' => 'jS M Y'
            , 'pages_order' => 'meta.title:desc'
            , 'timezone' => date_default_timezone_get()
            , 'charset' => 'UTF-8'
            , 'display_errors' => 0
            , 'content_dir' => ROOT_DIR . DS . 'content'
            , 'content_ext' => '.md'
            , 'themes_dir' => ROOT_DIR . DS . 'themes'
            , 'cache_dir' => ROOT_DIR . DS . 'var' . DS . 'cache'
            , 'storage_dir' => ROOT_DIR . DS . 'var' . DS . 'datastorage'
            , 'plugins' => [
                'phile\\errorHandler' => [
                    'active' => true,
                    'handler' => \Phile\Plugin\Phile\ErrorHandler\Plugin::HANDLER_DEVELOPMENT
                ],
                'phile\\setupCheck' => ['active' => true],
                'phile\\parserMarkdown' => ['active' => true],
                'phile\\parserMeta' => [
                    'active' => true,
                    'format' => 'Phile'
                ],
                'phile\\templateTwig' => ['active' => true],
                'phile\\phpFastCache' => ['active' => true],
                'phile\\simpleFileDataPersistence' => ['active' => true]
            ]
        ];

        $this->settings = array_replace_recursive($defaults, $configuration);

        Registry::set('Phile_Settings', $this->settings);
        date_default_timezone_set($this->settings['timezone']);
    }

    /**
     * auto-setup of files and folders
     */
    protected function initializeFilesAndFolders()
    {
        $dirs = [
            $this->settings['cache_dir']
            , $this->settings['storage_dir']
        ];

        foreach ($dirs as $dir) {
            $path = realpath($dir);
            if (empty($path) || strpos($path, ROOT_DIR) === false) {
                continue;
            }

            if (!file_exists($path)) {
                mkdir($path, 0775, true);
            }
        }
    }

    /**
     * initialize plugins
     *
     * @throws Exception\PluginException
     */
    protected function initializePlugins()
    {
        $loader = new PluginRepository();
        if (isset($this->settings['plugins']) && is_array($this->settings['plugins'])) {
            $this->plugins = $loader->loadAll($this->settings['plugins']);
        }

        Event::triggerEvent('plugins_loaded', ['plugins' => $this->plugins]);

        // throw not earlier to have the error-handler plugin loaded
        // and initialized (by 'plugins_loaded' event)
        $errors = $loader->getLoadErrors();
        if (count($errors) > 0) {
            throw new PluginException($errors[0]['message'], $errors[0]['code']);
        }

        // settings now include initialized plugin-configs
        $this->settings = Registry::get('Phile_Settings');
        Event::triggerEvent('config_loaded', ['config' => $this->settings]);
    }

    /**
     * method to get plugins
     * @return array
     * @deprecated since 1.5 will be removed
     * @use 'plugins_loaded' event
     */
    public function getPlugins()
    {
        return $this->plugins;
    }
}
