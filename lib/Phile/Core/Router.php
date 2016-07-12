<?php
/**
 * the Router class
 */

namespace Phile\Core;

use Phile\Event\RoutingEvent;
use Phile\ServiceLocator\RouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * this Router class is responsible for Phile's basic URL management
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Core
 */
class Router implements RouterInterface
{

    /** @var array Phile global settings */
    private $settings;

    /**
     * @var array with $_SERVER environment
     */
    private $server;

    /**
     * @var EventDispatcherInterface Event dispatcher
     */
    private $dispatcher;

    /**
     * @param array $settings Phile global settings
     * @param EventDispatcherInterface $dispatcher
     * @param array $server $_SERVER environment
     */
    public function __construct(array $settings, EventDispatcherInterface $dispatcher, array $server)
    {
        $this->settings = $settings;
        $this->server = $server;
        $this->dispatcher = $dispatcher;
    }

    public function match($url){
        $url = $this->normalizeUrl($url);

        $event = new RoutingEvent($url);
        $this->dispatcher->dispatch(RoutingEvent::BEFORE, $event);

        if ($event->getContentPath() === null){
            $url = $event->getRequestUrl();
            $contentPath = $this->resolvePath($url);
            if (!$this->needsRedirect($url, $contentPath)) {
                $event->setContentPath($contentPath);
            }
        }

        $this->dispatcher->dispatch(RoutingEvent::AFTER, $event);

        return $event->getContentPath();
    }

    public function matchRedirect($url)
    {
        $url = $this->normalizeUrl($url);
        $redirect = null;
        $contentFile = $this->resolvePath($url);
        if ($contentFile && $this->needsRedirect($url, $contentFile)) {
            $redirect = $this->urlForPath($contentFile);
        }

        return $redirect;
    }

    private function needsRedirect($url, $contentFile){
        $default = DIRECTORY_SEPARATOR . 'index' . $this->settings['content_ext'];

        $root = $url === "";
        $endsInSlash = substr($url, -1) === '/';
        $isDefaultFile = substr($contentFile, -strlen($default)) === $default;

        return !$root && !$endsInSlash && $isDefaultFile;
    }

    private function normalizeUrl($url){
        $queryPos = strpos($url, '?');
        if ($queryPos !== false){
            $url = substr($url, 0, $queryPos);
        }

        $url = ltrim($url, '/');
        $url = rawurldecode($url);

        return $url;
    }

    private function resolvePath($path){
        $contentDir = $this->settings['content_dir'];
        $contentExt = $this->settings['content_ext'];
        $base = $contentDir . str_replace('/', DIRECTORY_SEPARATOR, $path);

        $path = $base . $contentExt;
        if (file_exists($path) && is_file($path)){
            return $path;
        }

        $path = $base . 'index' . $contentExt;
        if (file_exists($path) && is_file($path)){
            return $path;
        }

        return null;
    }

    /**
     * Get the URL content path
     *
     * e.g. `sub/index.md` --> `http://host/phile-root/sub`
     *
     * @param  string $path
     * @param  bool   $absolute   return a full or root-relative URL
     * @return string URL
     */
    public function urlForPath($path, $absolute = true)
    {
        if (strpos($path, $this->settings['content_dir']) === 0){
            $path = substr($path, strlen($this->settings['content_dir']));
            $path = ltrim($path, DIRECTORY_SEPARATOR);
        }

        $contentExt = $this->settings['content_ext'];
        $path = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $path);

        if (substr($path, -strlen($contentExt)) === $contentExt){
            $path = substr($path, 0, -strlen($contentExt));
        }

        $default = '/index';
        if (substr($path, -strlen($default)) === $default){
            $path = substr($path, 0, -strlen($default) + 1);
        }

        $url = $path;
        if ($absolute){
            $url = $this->settings['base_url'] . $url;
        }

        return $url;
    }
}
