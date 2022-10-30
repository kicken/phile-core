<?php

namespace Phile\Test;

class TemporaryRootDirectory {
    private $root;

    public function __construct(){
        $this->createTempRoot();
        $this->build();
    }

    public function __destruct(){
        $this->remove();
    }

    public function getRoot() : string{
        return $this->root;
    }

    private function createTempRoot() : void{
        $root = tempnam(sys_get_temp_dir(), 'phile-test');
        @unlink($root);
        if (!mkdir($root, 0755)){
            throw new \RuntimeException('Unable to generate content root.');
        }

        $this->root = $root;
    }

    private function build() : void{
        $root = $this->root . '/';
        $fileList = [
            'content/index.md'
            , 'content/about.md'
            , 'content/sub/index.md'
            , 'content/sub/page/index.md'
            , 'content/sub/page/test.md'
            , 'public/themes/default/index.html'
        ];
        foreach ($fileList as $file){
            $fullPath = $root . $file;
            $fullDir = dirname($fullPath);
            if (!file_exists($fullDir)){
                if (!mkdir($fullDir, 0755, true)){
                    throw new \RuntimeException('Unable to generate content file.');
                }
            }

            file_put_contents($fullPath, $file);
        }
    }

    private function remove(){
        $iter = new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO);
        $iter = new \RecursiveIteratorIterator($iter, \RecursiveIteratorIterator::CHILD_FIRST);
        /** @var \SplFileInfo $item */
        foreach ($iter as $item){
            if ($item->isDir()){
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($this->root);
    }
}
