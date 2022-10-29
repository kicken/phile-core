<?php
/**
 * the page repository class
 */

namespace Phile\Repository;

use FilesystemIterator;
use Phile\Core;
use Phile\FilterIterator\ContentFileFilterIterator;
use Phile\Model\Page as PageModel;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class Page {
    protected $core;

    /**
     * @var array object storage for initialized objects, to prevent multiple loading of objects.
     */
    protected $storage = [];

    /**
     * @var CacheInterface the cache implementation
     */
    protected $cache = null;

    public function __construct(Core $core){
        $this->core = $core;
    }

    public function findByPath(string $pageId, string $folder = null) : ?PageModel{
        if ($folder === null){
            $folder = $this->core->getSetting('content_dir');
        }

        // be merciful to lazy third-party-usage and accept a leading slash
        $pageId = ltrim($pageId, '/');
        // 'sub/' should serve page 'sub/index'
        if ($pageId === '' || substr($pageId, -1) === '/'){
            $pageId .= 'index';
        }

        $contentExt = $this->core->getSetting('content_ext', '.md');
        $file = $folder . $pageId . $contentExt;
        if (!file_exists($file)){
            if (substr($pageId, -6) === '/index'){
                // try to resolve subdirectory 'sub/' to page 'sub'
                $pageId = substr($pageId, 0, strlen($pageId) - 6);
            } else {
                // try to resolve page 'sub' to subdirectory 'sub/'
                $pageId .= '/index';
            }
            $file = $folder . $pageId . $contentExt;
        }
        if (!file_exists($file)){
            return null;
        }

        return $this->getPage($file);
    }

    /**
     * find all pages (*.md) files and returns an array of Page models
     *
     * @param array $options
     * @param ?string $folder
     *
     * @return PageCollection of \Phile\Model\Page objects
     */
    public function findAll(array $options = [], string $folder = null) : PageCollection{
        if ($folder === null){
            $folder = $this->core->getSetting('content_dir');
        }

        return new PageCollection(
            function() use ($options, $folder){
                // ignore files with a leading '.' in its filename
                $files = $this->getFiles($folder);
                $pages = [];

                foreach ($files as $file){
                    $pages[] = $this->getPage($file);
                }

                if ($options['pages_order'] ?? null){
                    $pages = $this->sortPages($pages, $options['pages_order']);
                }

                return $pages;
            }
        );
    }

    /**
     * get page from cache or file path
     *
     * @param string $filePath
     *
     * @return mixed|PageModel
     * @throws InvalidArgumentException
     */
    protected function getPage(string $filePath) : ?PageModel{
        $key = 'Phile_Model_Page_' . md5($filePath);
        if (isset($this->storage[$key])){
            return $this->storage[$key];
        }

        if ($this->cache !== null){
            if ($this->cache->has($key)){
                $page = $this->cache->get($key);
            } else {
                $page = $this->createPageModel($filePath);
                $this->cache->set($key, $page);
            }
        } else {
            $page = $this->createPageModel($filePath);
        }
        $this->storage[$key] = $page;

        return $page;
    }

    private function createPageModel(string $filePath) : PageModel{
        return new PageModel($this->core, $filePath);
    }

    private function getFiles(string $folder) : array{
        $directoryIterator = new \RecursiveDirectoryIterator($folder, FilesystemIterator::FOLLOW_SYMLINKS);
        $recursiveIterator = new \RecursiveIteratorIterator($directoryIterator);
        $filterIterator = new ContentFileFilterIterator($recursiveIterator, $this->core->getSetting('content_ext', '.md'));

        $result = [];
        foreach ($filterIterator as $file){
            $result[] = $file->getPathname();
        }

        return $result;
    }

    private function parseSortCriteria(string $criteria) : array{
        $terms = preg_split('/\s+/', $criteria, -1, PREG_SPLIT_NO_EMPTY);
        $sorting = [];
        foreach ($terms as $term){
            $sub = explode('.', $term);
            if (count($sub) > 1){
                $type = array_shift($sub);
            } else {
                $type = null;
            }
            $sub = explode(':', $sub[0]);
            if (count($sub) === 1){
                $sub[1] = 'asc';
            }
            $sorting[] = ['type' => $type, 'key' => $sub[0], 'order' => $sub[1], 'string' => $term];
        }

        return $sorting;
    }

    private function sortPages(array $pages, string $criteria) : array{
        // parse search	criteria
        $sorting = $this->parseSortCriteria($criteria);

        // prepare search criteria for array_multisort
        foreach ($sorting as $sort){
            $key = $sort['key'];
            $column = [];

            /** @var PageModel $page */
            foreach ($pages as $page){
                $meta = $page->getMeta();
                $value = $meta[$key];
                $column[] = $value;
            }

            $sortHelper[] = $column;
            $sortHelper[] = constant('SORT_' . strtoupper($sort['order']));
        }
        $sortHelper[] = &$pages;

        call_user_func_array('array_multisort', $sortHelper);

        return $pages;
    }
}
