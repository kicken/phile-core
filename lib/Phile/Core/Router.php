<?php

namespace Phile\Core;

use Phile\Core;
use Phile\Event\RoutingEvent;
use Phile\Service\RouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Router implements RouterInterface {
    private Core $core;

    public function __construct(Core $core){
        $this->core = $core;
    }

    public function match(string $url) : ?string{
        $url = $this->normalizeUrl($url);
        $dispatcher = $this->core->getService(EventDispatcherInterface::class);

        $event = new RoutingEvent($url);
        $dispatcher->dispatch($event, RoutingEvent::BEFORE);

        if ($event->getContentPath() === null){
            $url = $event->getRequestUrl();
            $contentPath = $this->resolvePath($url);
            if (!$this->needsRedirect($url, $contentPath)){
                $event->setContentPath($contentPath);
            }
        }

        $dispatcher->dispatch($event, RoutingEvent::AFTER);

        return $event->getContentPath();
    }

    public function matchRedirect(string $url) : ?string{
        $url = $this->normalizeUrl($url);
        $redirect = null;
        $contentFile = $this->resolvePath($url);
        if ($contentFile && $this->needsRedirect($url, $contentFile)){
            $redirect = $this->urlForPath($contentFile);
        }

        return $redirect;
    }

    private function getContentExt(){
        $ext = $this->core->getSetting('content_ext', '.md');
        if ($ext[0] !== '.'){
            $ext = '.' . $ext;
        }

        return $ext;
    }

    private function needsRedirect(string $url, ?string $contentFile) : bool{
        $root = $url === "";
        $endsInSlash = str_ends_with($url, '/');
        $isDefaultFile = false;
        if ($contentFile !== null){
            $default = DIRECTORY_SEPARATOR . 'index' . $this->getContentExt();
            $isDefaultFile = str_ends_with($contentFile, $default);
        }

        return !$root && !$endsInSlash && $isDefaultFile;
    }

    private function normalizeUrl(string $url) : string{
        $queryPos = strpos($url, '?');
        if ($queryPos !== false){
            $url = substr($url, 0, $queryPos);
        }

        $url = '/' . ltrim($url, '/');
        $url = rawurldecode($url);

        return $url;
    }

    private function resolvePath(string $path) : ?string{
        $contentDir = $this->core->getSetting('content_dir');
        $contentExt = $this->getContentExt();

        $contentDir = rtrim($contentDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $path = ltrim($path, DIRECTORY_SEPARATOR);
        $base = $contentDir . $path;

        $file = $base . $contentExt;
        if (file_exists($file) && is_file($file)){
            return $file;
        }

        if (str_ends_with($base, DIRECTORY_SEPARATOR)){
            $file = $base . 'index' . $contentExt;
        } else {
            $file = $base . DIRECTORY_SEPARATOR . 'index' . $contentExt;
        }
        if (file_exists($file) && is_file($file)){
            return $file;
        }

        return null;
    }

    /**
     * Get the URL content path
     *
     * e.g. `sub/index.md` --> `http://host/phile-root/sub`
     *
     * @param string $path
     * @param bool $absolute return a full or root-relative URL
     *
     * @return string URL
     */
    public function urlForPath(string $path, bool $absolute = true) : string{
        $contentDir = $this->core->getSetting('content_dir');
        if (str_starts_with($path, $contentDir)){
            $path = substr($path, strlen($contentDir));
            $path = ltrim($path, DIRECTORY_SEPARATOR);
        }

        $contentExt = $this->getContentExt();
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        if (!str_starts_with($path, '/')){
            $path = '/' . $path;
        }

        if (str_ends_with($path, $contentExt)){
            $path = substr($path, 0, -strlen($contentExt));
        }

        $default = '/index';
        if (str_ends_with($path, $default)){
            $path = substr($path, 0, -strlen($default) + 1);
        }

        $url = $path;
        if ($absolute){
            $baseUrl = $this->core->getSetting('base_url');
            if (str_ends_with($baseUrl, '/')){
                $baseUrl = rtrim($baseUrl, '/');
            }
            $url = $baseUrl . $url;
        }

        return $url;
    }
}
