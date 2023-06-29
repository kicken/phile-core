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
use Phile\Model\Page;
use Phile\Plugin\AbstractPlugin;
use Phile\Plugin\ErrorHandler\ErrorHandlerPlugin;
use Phile\Plugin\ParserMarkdown\MarkdownPlugin;
use Phile\Plugin\ParserMeta\MetaParserPlugin;
use Phile\Plugin\PhpFastCache\FastCachePlugin;
use Phile\Plugin\SimpleFileDataPersistence\FileDataPersistencePlugin;
use Phile\Plugin\TemplateTwig\TwigTemplatePlugin;
use Phile\Service\RouterInterface;
use Phile\Service\TemplateInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Phile Core class
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile
 */
class Core {
    private $settings;
    private $services;

    public function __construct(array $settings){
        $this->settings = $this->mergeDefaultConfiguration($settings);
        $this->createDefaultServices();
        $dispatcher = $this->getService(EventDispatcherInterface::class);

        $pluginList = $this->settings['plugins'] ?? [];
        uasort($pluginList, function(array $a, array $b){
            return -1 * (($a['priority'] ?? 1) <=> ($b['priority'] ?? 1));
        });
        foreach ($pluginList as $pluginClass => $pluginConfig){
            if (!($pluginConfig['active'] ?? true)){
                continue;
            }

            if (class_exists($pluginClass) && is_a($pluginClass, AbstractPlugin::class, true)){
                $plugin = new $pluginClass($this, $pluginConfig);
                if ($plugin instanceof EventSubscriberInterface){
                    $dispatcher->addSubscriber($plugin);
                }
            }
        }

        $event = new CoreEvent();
        $dispatcher->dispatch($event,CoreEvent::LOADED);
    }

    private function mergeDefaultConfiguration(array $userSettings) : array{
        $root = $userSettings['root_dir'] ?? dirname(getcwd());
        $config = array_replace([
            'base_url' => sprintf('http%s://%s', ($_SERVER['HTTPS'] ?? false) ? 's' : '', $_SERVER['HTTP_HOST'] ?? 'localhost'),
            'site_title' => 'PhileCMS',
            'theme' => 'default',
            'date_format' => 'jS M Y',
            'pages_order' => 'meta.title:desc',
            'timezone' => date_default_timezone_get(),
            'charset' => 'utf-8',
            'display_errors' => 0,
            'root_dir' => $root,
            'content_dir' => $root . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR,
            'content_ext' => '.md',
            'themes_dir' => $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR,
            'cache_dir' => $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
            'public_dir' => $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR,
            'storage_dir' => $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'datastorage' . DIRECTORY_SEPARATOR
        ], $userSettings);
        $config['base_url'] = rtrim($config['base_url'], '/');

        $config['plugins'] = array_replace_recursive([
            ErrorHandlerPlugin::class => ['active' => true, 'priority' => PHP_INT_MAX],
            MarkdownPlugin::class => ['active' => true],
            MetaParserPlugin::class => ['active' => true],
            TwigTemplatePlugin::class => [
                'active' => true,
                'theme' => 'default',
                'themes_dir' => $config['themes_dir'],
                'template_extension' => 'html',
                'options' => [
                    'cache' => false,
                    'autoescape' => false
                ]
            ],
            FastCachePlugin::class => [
                'active' => true,
                'driver' => 'files',
                'path' => $config['cache_dir']
            ],
            FileDataPersistencePlugin::class => [
                'active' => true,
                'storage_dir' => $config['storage_dir']
            ]
        ], $config['plugins'] ?? []);

        return $config;
    }

    public function processRequest(string $url) : void{
        $url = $this->normalizeRequestUrl($url);
        $router = $this->getService(RouterInterface::class);

        $contentFile = $router->match($url);
        if ($contentFile === null){
            $redirect = $router->matchRedirect($url);
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

    private function handleHttpStatus(int $status) : Response{
        $event = new NotFoundEvent($_SERVER['REQUEST_URI']);
        $this->getService(EventDispatcherInterface::class)->dispatch($event, NotFoundEvent::AFTER);

        $contentFile = $this->getService(RouterInterface::class)->match('/' . $status);
        if ($contentFile === null){
            $response = new Response();
            $response
                ->setStatusCode(404)
                ->setHeader('Content-type', 'text/html; charset=' . $this->settings['charset'])
                ->setBody('Not found');
        } else {
            $response = $this->createResponse($contentFile)
                ->setHeader('Content-type', 'text/html; charset=' . $this->settings['charset'])
                ->setStatusCode($status);
        }

        return $response;
    }

    private function createResponse(string $contentFile) : Response{
        $response = new Response();

        $extension = pathinfo($contentFile, PATHINFO_EXTENSION);
        if ($this->isContentExtension($extension)){
            $response
                ->setBody($this->handleContentFile($contentFile))
                ->setHeader('Content-type', 'text/html; charset=' . $this->settings['charset'])
                ->setStatusCode(200);
        } else {
            $stream = fopen($contentFile, 'r');
            if (!$stream){
                $response = $this->handleHttpStatus(403);
            } else {
                $response
                    ->setBody($stream)
                    ->setStatusCode(200);

                $mimeType = $this->guessMimeType($contentFile);
                if ($mimeType){
                    $response->setHeader('Content-type', $mimeType);
                }
            }
        }

        return $response;
    }

    private function isContentExtension(string $extension) : bool{
        return $this->normalizeExtension($extension) === $this->normalizeExtension($this->settings['content_ext']);
    }

    private function normalizeExtension(string $extension) : string{
        $extension = strtolower($extension);
        $extension = trim($extension, '.');

        return $extension;
    }

    private function guessMimeType(string $file) : string{
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

        if ($type === 'text/plain'){
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $type = [
                'js' => 'text/javascript',
                'css' => 'text/css'
            ][$ext] ?? $type;
        }

        return $type;
    }

    private function handleContentFile(string $contentFile) : string{
        $template = $this->getService(TemplateInterface::class);
        $dispatcher = $this->getService(EventDispatcherInterface::class);
        $page = $this->createPageModel($contentFile);

        $event = new RenderingEvent($page, $template, $page->getParsedContent());
        $dispatcher->dispatch($event, RenderingEvent::BEFORE);

        $output = $template->render($page);
        $event->setContent($output);

        $dispatcher->dispatch($event, RenderingEvent::AFTER);
        $output = $event->getContent();

        return $output;
    }

    private function createPageModel(string $contentFile) : Page{
        return new Page($this, $contentFile);
    }

    private function outputResponse(Response $response) : void{
        http_response_code($response->getStatusCode());
        $this->outputHeaders($response->getHeaders());

        $body = $response->getBody();
        if (is_resource($body)){
            fpassthru($body);
        } else {
            echo $body;
        }
    }

    private function outputHeaders(array $headers) : void{
        foreach ($headers as $name => $value){
            header(sprintf("%s: %s", $name, $value));
        }
    }

    private function normalizeRequestUrl(string $url) : string{
        $baseUrl = $this->settings['base_url'];
        $url = '/' . ltrim($url, '/');

        $basePath = parse_url($baseUrl, PHP_URL_PATH) ?? '/';
        $basePath = '/' . ltrim($basePath, '/');

        if (stripos($url, $basePath) === 0){
            $url = substr($url, strlen($basePath));
            $url = '/' . ltrim($url, '/');
        }

        return $url;
    }

    private function createDefaultServices() : void{
        $this->registerService(EventDispatcherInterface::class, new EventDispatcher());
        $this->registerService(RouterInterface::class, new Router($this));
    }

    public function registerService(string $interface, $object) : self{
        if (!interface_exists($interface)){
            throw new \RuntimeException('Interface ' . $interface . ' does not exist.');
        }
        if (!is_a($object, $interface)){
            throw new \LogicException('Service does not implement ' . $interface . '.');
        }

        $this->services[$interface] = $object;

        return $this;
    }

    public function hasService(string $interface) : bool{
        return array_key_exists($interface, $this->services);
    }

    public function getService(string $interface){
        if (!$this->hasService($interface)){
            throw new \RuntimeException('Service ' . $interface . ' is not registered.');
        }

        return $this->services[$interface];
    }

    public function getSetting(string $path, $default = null){
        $path = explode('.', $path);
        $value = null;
        for ($i = 0, $len = count($path); $i < $len; $i++){
            $value = ($i === 0 ? $this->settings : $value)[$path[$i]] ?? null;
        }

        return $value ?? $default;
    }
}
