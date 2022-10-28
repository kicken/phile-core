<?php

namespace Phile\Test\FilterIterator;

use Phile\FilterIterator\ContentFileFilterIterator;
use Phile\Test\TemporaryContentDirectory;
use PHPUnit\Framework\TestCase;

/**
 * class ContentFileFilterIteratorTest
 *
 * @author  Phile CMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 */
class ContentFileFilterIteratorTest extends TestCase {
    use TemporaryContentDirectory;

    private static $contentRoot;

    public static function setUpBeforeClass() : void{
        try {
            self::$contentRoot = self::buildContentDir();
        } catch (\RuntimeException $exception){
            self::markTestSkipped($exception->getMessage());
        }
    }

    public static function tearDownAfterClass() : void{
        self::removeContentDir(self::$contentRoot);
    }

    public function testContentFileFilterIterator(){
        $files = new ContentFileFilterIterator(new \DirectoryIterator(self::$contentRoot), 'md');
        $result = [];
        /** @var \SplFileInfo $file */
        foreach ($files as $file){
            $result[] = $file->getFilename();
        }

        sort($result);
        $this->assertEquals(['about.md', 'index.md'], $result);
    }
}
