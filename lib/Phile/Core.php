<?php
/**
 * the core of Phile
 */
namespace Phile;

use Phile\Core\Registry;
use Phile\Core\Response;
use Phile\Core\Router;
use Phile\Core\ServiceLocator;
use Phile\Event\NotFoundEvent;
use Phile\Event\RenderingEvent;
use Phile\Exception\PluginInitializationException;
use Phile\Exception\PluginNotFoundException;
use Phile\Model\Page;
use Phile\Plugin\ErrorHandler\ErrorHandlerPlugin;
use Phile\Plugin\ParserMarkdown\MarkdownPlugin;
use Phile\Plugin\ParserMeta\MetaParserPlugin;
use Phile\Plugin\PhpFastCache\FastCachePlugin;
use Phile\Plugin\SimpleFileDataPersistence\FileDataPersistencePlugin;
use Phile\Plugin\TemplateTwig\TwigTemplatePlugin;
use Phile\ServiceLocator\TemplateInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Phile Core class
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile
 */
class Core
{
    /**
     * @var array the settings array
     */
    protected $settings;

    /**
     * @var array the loaded plugins
     */
    protected $plugins;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @param array $config
     * @param array $plugins
     * @throws \Exception
     */
    public function __construct(array $config, array $plugins)
    {
        $this->settings = $config;
        $this->plugins = $plugins;
        $this->dispatcher = new EventDispatcher();
        $this->router = new Router($this->settings, $this->dispatcher, $_SERVER);

        Registry::set('Phile_EventDispatcher', $this->dispatcher);
        Registry::set('Phile_Settings', $this->settings);
    }

    /**
     * @param array $config
     * @return Core
     */
    public static function bootstrap($config = [])
    {
        $rootDirectory = isset($config['root_dir'])?$config['root_dir']:self::findRootDirectory();
        $baseUrl = isset($config['base_url'])?$config['base_url']:self::findBaseUrl();

        $defaultConfiguration = self::defaultConfiguration($rootDirectory, $baseUrl);
        $config = array_replace_recursive($defaultConfiguration, $config);

        $plugins = static::loadPlugins($config['plugins']);

        return new static($config, $plugins);
    }

    protected static function defaultConfiguration($root, $baseUrl)
    {
        $defaults = [
            'base_url' => $baseUrl,
            'site_title' => 'PhileCMS',
            'theme' => 'default',
            'date_format' => 'jS M Y',
            'pages_order' => 'meta.title:desc',
            'timezone' => date_default_timezone_get(),
            'charset' => 'utf-8',
            'display_errors' => 0,
            'content_dir' => $root . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR,
            'content_ext' => '.md',
            'themes_dir' => $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR,
            'cache_dir' => $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
            'public_dir' => $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR,
            'storage_dir' => $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'datastorage' . DIRECTORY_SEPARATOR
        ];

        $defaults['plugins'] = [
            ErrorHandlerPlugin::class => [
                'active' => true,
                'handler' => Plugin\ErrorHandler\ErrorHandlerPlugin::HANDLER_DEVELOPMENT
            ],
            MarkdownPlugin::class => ['active' => true],
            MetaParserPlugin::class => [
                'active' => true
            ],
            TwigTemplatePlugin::class => [
                'active' => true,
                'theme' => $defaults['theme'],
                'themes_dir' => $defaults['themes_dir'],
                'template_extension' => 'html',
                'options' => [
                    'cache' => false,
                    'autoescape' => false
                ]
            ],
            FastCachePlugin::class => [
                'active' => true,
                'driver' => 'auto',
                'path' => $defaults['cache_dir']
            ],
            FileDataPersistencePlugin::class => [
                'active' => true,
                'storage_dir' => $defaults['storage_dir']
            ]
        ];

        return $defaults;
    }

    /**
     * @return string
     */
    protected static function findRootDirectory()
    {
        //Attempt to determine the root directory location automatically.
        $rootDirectory = dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . '..';
        $rootDirectory = (string)realpath($rootDirectory);

        return $rootDirectory;
    }

    protected static function findBaseUrl()
    {
        $url = '';
        if (isset($_SERVER['PHP_SELF'])) {
            $url = preg_replace('/index\.php(.*)?$/', '', $_SERVER['PHP_SELF']);
        }

        $https = isset($_SERVER['HTTPS']) && $_SERVER['https'] !== 'off';
        $protocol = $https?'https':'http';
        $host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:null;

        if ($protocol && $host) {
            $url = sprintf('%s://%s/%s', $protocol, $host, ltrim($url, '/'));
        }

        $url = rtrim($url, '/');

        return $url;
    }

    protected static function loadPlugins($config)
    {
        $plugins = [];
        foreach ($config['plugins'] as $class => $pluginSpecificConfig) {
            if (isset($pluginSpecificConfig['active']) && !$pluginSpecificConfig['active']) {
                continue;
            }

            if (!class_exists($class)) {
                throw new PluginNotFoundException($class);
            }

            try {
                unset($pluginSpecificConfig['active']);
                $plugins[] = new $class($pluginSpecificConfig, $config);
            } catch (\Exception $ex){
                throw new PluginInitializationException('Exception caught while trying to instantiate plugin', $ex);
            }
        }

        return $plugins;
    }

    public function handleRequest()
    {
        $contentFile = $this->router->match($_SERVER['REQUEST_URI']);
        if ($contentFile === null){
            $response = $this->handleNotFound();
        } else {
            $response = $this->handleContentFile($contentFile);
        }

        $this->outputResponse($response);
    }

    private function handleNotFound()
    {
        $event = new NotFoundEvent($_SERVER['REQUEST_URI']);
        $this->dispatcher->dispatch(NotFoundEvent::AFTER, $event);

        $contentFile = $this->router->match('/404');
        if ($contentFile === null) {
            $response = new Response();
            $response
                ->setCharset('utf-8')
                ->setStatusCode(404)
                ->setBody('Not found')
            ;
        } else {
            $response = $this->handleContentFile($contentFile);
            $response->setStatusCode(404);
        }

        return $response;
    }

    private function handleContentFile($contentFile)
    {
        /** @var TemplateInterface $template */
        $template = Registry::get('Phile_Template');
        $page = $this->createPageModel($contentFile);

        $event = new RenderingEvent($page, $template, $page->getParsedContent());
        $this->dispatcher->dispatch(RenderingEvent::BEFORE, $event);

        $output = $template->render($page);
        $event->setContent($output);

        $this->dispatcher->dispatch(RenderingEvent::AFTER, $event);
        $output = $event->getContent();

        $response = new Response();
        $response
            ->setStatusCode(200)
            ->setCharset($this->settings['charset'])
            ->setBody($output)
        ;

        return $response;
    }

    private function createPageModel($contentFile)
    {
        $parser = Registry::get('Phile_Parser');
        $metaParser = Registry::get('Phile_Parser_Meta');

        return new Page($this->settings, $this->dispatcher, $parser, $metaParser, $contentFile);
    }

    private function outputResponse(Response $response){
        http_response_code($response->getStatusCode());
        $this->outputHeaders($response->getHeaders());
        echo $response->getBody();
    }

    private function outputHeaders($headers){
        foreach ($headers as $name=>$value){
            header($name, $value);
        }
    }

    /**
     * initialize error handling
     */
    protected function initializeErrorHandling()
    {
        if (ServiceLocator::hasService('Phile_ErrorHandler')) {
            $errorHandler = ServiceLocator::getService('Phile_ErrorHandler');
            set_error_handler([$errorHandler, 'handleError']);
            register_shutdown_function([$errorHandler, 'handleShutdown']);
            ini_set('display_errors', $this->settings['display_errors']);
        }
    }
}
