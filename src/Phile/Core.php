<?php
/**
 * the core of Phile
 */

namespace Phile;

use Phile\Core\Response;
use Phile\Core\Router;
use Phile\Event\CoreEvent;
use Phile\Event\NotFoundEvent;
use Phile\Event\RenderingEvent;
use Phile\Exception\PluginInitializationException;
use Phile\Exception\PluginNotFoundException;
use Phile\Model\Page;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Phile Core class
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile
 */
class Core {
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @param $container
     * @throws \Exception
     */
    public function __construct(ContainerInterface $container){
        $this->router = new Router($this->settings, $this->dispatcher, $_SERVER);

        /** @var AbstractPlugin $plugin */
        foreach ($plugins as $plugin){
            $this->dispatcher->addSubscriber($plugin);
        }

        ServiceLocator::registerService('Phile_Router', $this->router);
        Registry::set('Phile_Settings', $this->settings);

        $errorHandler = ServiceLocator::getService('Phile_ErrorHandler');
        if ($errorHandler){
            set_error_handler([$errorHandler, 'handleError']);
            set_exception_handler([$errorHandler, 'handleException']);
        }

        $event = new CoreEvent();
        $this->dispatcher->dispatch(CoreEvent::LOADED, $event);
    }

    /**
     * @param array $config
     * @return Core
     */
    public static function bootstrap($config = []){
        ServiceLocator::registerService('Phile_EventDispatcher', new EventDispatcher());
        $rootDirectory = isset($config['root_dir'])?$config['root_dir']:self::findRootDirectory();
        $baseUrl = isset($config['base_url'])?$config['base_url']:self::findBaseUrl();

        $defaultConfiguration = self::defaultConfiguration($rootDirectory, $baseUrl);
        $config = array_replace_recursive($defaultConfiguration, $config);

        $config = self::defaultPluginsConfiguration($config);
        $plugins = static::loadPlugins($config);

        return new static($config, $plugins);
    }

    protected static function defaultConfiguration($root, $baseUrl){
        $defaults = [
            'base_url' => $baseUrl,
            'site_title' => 'PhileCMS',
            'theme' => 'default',
            'pages_order' => 'meta.title:desc',
            'charset' => 'utf-8',
            'content_dir' => $root . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR,
            'content_ext' => '.md',
        ];

        return $defaults;
    }

    /**
     * @return string
     */
    protected static function findRootDirectory(){
        //Attempt to determine the root directory location automatically.
        $rootDirectory = dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . '..';
        $rootDirectory = (string)realpath($rootDirectory);

        return $rootDirectory;
    }

    protected static function findBaseUrl(){
        $url = '';
        if (isset($_SERVER['PHP_SELF'])){
            $url = preg_replace('/index\.php(.*)?$/', '', $_SERVER['PHP_SELF']);
        }

        $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $protocol = $https?'https':'http';
        $host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:null;

        if ($protocol && $host){
            $url = sprintf('%s://%s/%s', $protocol, $host, ltrim($url, '/'));
        }

        $url = rtrim($url, '/');

        return $url;
    }

    protected static function loadPlugins($config){
        $plugins = [];
        foreach ($config['plugins'] as $class => $pluginSpecificConfig){
            if (isset($pluginSpecificConfig['active']) && !$pluginSpecificConfig['active']){
                continue;
            }

            if (!class_exists($class)){
                throw new PluginNotFoundException($class);
            }

            try {
                unset($pluginSpecificConfig['active']);
                $plugins[] = new $class($pluginSpecificConfig, $config);
            } catch (\Exception $ex){
                throw new PluginInitializationException($class, $ex);
            }
        }

        return $plugins;
    }

    public function handleRequest($url){
        $url = $this->normalizeRequestUrl($url);
        $contentFile = $this->router->match($url);
        if ($contentFile === null){
            $redirect = $this->router->matchRedirect($url);
            if (!$redirect){
                $response = $this->handleHttpStatus(404);
            } else {
                $response = new Response();
                $response->redirect($redirect);
            }
        } else {
            $response = $this->createResponse($contentFile);
        }

        $this->outputResponse($response);
    }

    private function handleHttpStatus($status){
        $event = new NotFoundEvent($_SERVER['REQUEST_URI']);
        $this->dispatcher->dispatch(NotFoundEvent::AFTER, $event);

        $contentFile = $this->router->match('/' . $status);
        if ($contentFile === null){
            $response = new Response();
            $response
                ->setStatusCode(404)
                ->setHeader('Content-type', 'text/html; charset=' . $this->settings['charset'])
                ->setBody('Not found')
            ;
        } else {
            $response = $this->createResponse($contentFile)
                ->setHeader('Content-type', 'text/html; charset=' . $this->settings['charset'])
                ->setStatusCode($status)
            ;
        }

        return $response;
    }

    private function createResponse($contentFile){
        $response = new Response();

        $extension = pathinfo($contentFile, PATHINFO_EXTENSION);
        if ($this->isContentExtension($extension)){
            $response
                ->setBody($this->handleContentFile($contentFile))
                ->setHeader('Content-type', 'text/html; charset=' . $this->settings['charset'])
                ->setStatusCode(200)
            ;
        } else {
            $stream = fopen($contentFile, 'r');
            if (!$stream){
                $response = $this->handleHttpStatus(403);
            } else {
                $response
                    ->setBody($stream)
                    ->setStatusCode(200)
                ;

                $mimeType = $this->guessMimeType($contentFile);
                if ($mimeType){
                    $response->setHeader('Content-type', $mimeType);
                }
            }
        }

        return $response;
    }

    private function isContentExtension($extension){
        return $this->normalizeExtension($extension) === $this->normalizeExtension($this->settings['content_ext']);
    }

    private function normalizeExtension($extension){
        $extension = strtolower($extension);
        $extension = trim($extension, '.');

        return $extension;
    }

    private function guessMimeType($file){
        $type = null;
        if (function_exists('mime_content_type')){
            $type = mime_content_type($file);
        }

        if (!$type && class_exists('\finfo')){
            $info = new \finfo(FILEINFO_MIME);
            $info = $info->file($file);
            if ($info){
                $type = $info;
            }
        }

        //Try image types
        if (!$type){
            $info = getimagesize($file);
            if (isset($info['mime'])){
                $type = $info['mime'];
            }
        }

        return $type;
    }

    private function handleContentFile($contentFile){
        /** @var TemplateInterface $template */
        $template = ServiceLocator::getService('Phile_Template');
        $page = $this->createPageModel($contentFile);

        $event = new RenderingEvent($page, $template, $page->getParsedContent());
        $this->dispatcher->dispatch(RenderingEvent::BEFORE, $event);

        $output = $template->render($page);
        $event->setContent($output);

        $this->dispatcher->dispatch(RenderingEvent::AFTER, $event);
        $output = $event->getContent();

        return $output;
    }

    private function createPageModel($contentFile){
        $parser = ServiceLocator::getService('Phile_Parser');
        $metaParser = ServiceLocator::getService('Phile_Parser_Meta');

        return new Page($this->settings, $this->dispatcher, $parser, $metaParser, $contentFile);
    }

    private function outputResponse(Response $response){
        $this->closeSession();
        $this->disableOutputBuffering();

        http_response_code($response->getStatusCode());
        $this->outputHeaders($response->getHeaders());

        $body = $response->getBody();
        if (is_resource($body)){
            fpassthru($body);
        } else {
            echo $body;
        }
    }

    private function outputHeaders($headers){
        foreach ($headers as $name => $value){
            header(sprintf("%s: %s", $name, $value));
        }
    }

    private function closeSession(){
        session_write_close();
    }

    private function disableOutputBuffering(){
        while (ob_get_level()){
            ob_end_flush();
        }
    }

    /**
     * initialize error handling
     */
    protected function initializeErrorHandling(){
        if (ServiceLocator::hasService('Phile_ErrorHandler')){
            $errorHandler = ServiceLocator::getService('Phile_ErrorHandler');
            set_error_handler([$errorHandler, 'handleError']);
            register_shutdown_function([$errorHandler, 'handleShutdown']);
            ini_set('display_errors', $this->settings['display_errors']);
        }
    }

    private function normalizeRequestUrl($url){
        $baseUrl = $this->settings['base_url'];
        $url = ltrim($url, '/');

        $basePath = parse_url($baseUrl, PHP_URL_PATH);
        $basePath = ltrim($basePath, '/');

        if (stripos($url, $basePath) === 0){
            $url = substr($url, strlen($basePath));
            $url = ltrim($url, '/');
        }

        return $url;
    }
}
