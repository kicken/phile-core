<?php

namespace Phile\Test\FilterIterator;

use Phile\FilterIterator\ContentFileFilterIterator;
use Phile\Test\TemporaryRootDirectory;
use PHPUnit\Framework\TestCase;

/**
 * class ContentFileFilterIteratorTest
 *
 * @author  Phile CMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 */
class ContentFileFilterIteratorTest extends TestCase {
    private static $root;

    public static function setUpBeforeClass() : void{
        try {
            self::$root = new TemporaryRootDirectory();
        } catch (\RuntimeException $exception){
            self::markTestSkipped($exception->getMessage());
        }
    }

    public function testContentFileFilterIterator(){
        $iter = new \DirectoryIterator(self::$root->getRoot() . DIRECTORY_SEPARATOR . 'content');
        $iter = new ContentFileFilterIterator($iter, '.md');
        $result = [];
        /** @var \SplFileInfo $file */
        foreach ($iter as $file){
            $result[] = $file->getFilename();
        }

        sort($result);
        $this->assertEquals(['about.md', 'index.md'], $result);
    }
}
