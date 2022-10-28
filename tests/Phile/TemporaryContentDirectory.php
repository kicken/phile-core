<?php

namespace Phile\Test;

trait TemporaryContentDirectory {
    protected static function buildContentDir() : string{
        $root = tempnam(sys_get_temp_dir(), 'phile-test');
        @unlink($root);
        if (!mkdir($root, 0755)){
            throw new \RuntimeException('Unable to generate content root.');
        }

        $root .= '/';
        $fileList = ['index.md', 'about.md', 'sub/index.md', 'sub/page/index.md', 'sub/page/test.md'];
        foreach ($fileList as $file){
            $fullPath = $root . $file;
            $fullDir = dirname($fullPath);
            if (!file_exists($fullDir)){
                if (!mkdir($fullDir, 0755)){
                    throw new \RuntimeException('Unable to generate content file.');
                }
            }

            file_put_contents($fullPath, $file);
        }

        return $root;
    }

    protected static function removeContentDir(string $contentRoot){
        $iter = new \RecursiveDirectoryIterator($contentRoot, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO);
        $iter = new \RecursiveIteratorIterator($iter, \RecursiveIteratorIterator::CHILD_FIRST);
        /** @var \SplFileInfo $item */
        foreach ($iter as $item){
            if ($item->isDir()){
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($contentRoot);
    }
}
