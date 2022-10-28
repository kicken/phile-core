<?php

namespace Phile\Test\Core;

use Phile\Core\Router;
use Phile\Event\RoutingEvent;
use Phile\Test\TemporaryContentDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * the Router class
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package PhileTest
 */
class RouterTest extends TestCase {
    use TemporaryContentDirectory;

    private static $contentRoot;
    private $router;

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

    protected function setUp() : void{
        try {
            $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
            $this->router = new Router([
                'content_dir' => self::$contentRoot
                , 'base_url' => 'http://test/'
                , 'content_ext' => 'md'
            ], $dispatcher);
        } catch (\RuntimeException $exception){
            $this->markTestSkipped($exception->getMessage());
        }
    }

    public function testUrlForPathAbsolute(){
        $page = 'sub/index.md';
        $expected = 'http://test/sub/';
        $result = $this->router->urlForPath($page);
        $this->assertEquals($expected, $result);
    }

    public function testUrlForPageRelative(){
        $page = 'sub/index.md';
        $expected = 'sub/';
        $result = $this->router->urlForPath($page, false);
        $this->assertEquals($expected, $result);
    }

    public function testMatchPrePostEvents(){
        $beforeHandler = $this->getMockBuilder(\stdClass::class)->addMethods(['__invoke'])->getMock();
        $beforeHandler->expects($this->once())->method('__invoke')->with($this->isInstanceOf(RoutingEvent::class));
        $afterHandler = $this->getMockBuilder(\stdClass::class)->addMethods(['__invoke'])->getMock();
        $afterHandler->expects($this->once())->method('__invoke')->with($this->isInstanceOf(RoutingEvent::class));

        $this->router->match('/');
    }

    public function testMatchRootIndex(){
        $result = $this->router->match('/');
        $this->assertEquals(self::$contentRoot . '/index.md', $result);
    }

    public function testMatchSubDirectoryNoSlash(){
        $this->assertEquals(self::$contentRoot . '/sub/index.md', $this->router->match('/sub'));
    }

    public function testMatchSubDirectoryWithSlash(){
        $this->assertEquals(self::$contentRoot . '/sub/index.md', $this->router->match('/sub/'));
    }

    public function testMatchSubSubDirectoryNoSlash(){
        $this->assertEquals(self::$contentRoot . '/sub/page/index.md', $this->router->match('/sub/page'));
    }

    public function testMatchSubSubDirectoryWithSlash(){
        $this->assertEquals(self::$contentRoot . '/sub/page/index.md', $this->router->match('/sub/page/'));
    }

    public function testMatchSubPage(){
        $this->assertEquals(self::$contentRoot . '/sub/page/test.md', $this->router->match('/sub/page/test.md'));
    }
}
