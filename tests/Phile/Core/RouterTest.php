<?php

namespace Phile\Test\Core;

use Phile\Core;
use Phile\Core\Router;
use Phile\Event\RoutingEvent;
use Phile\Test\TemporaryRootDirectory;
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
    private static $root;
    private $router;
    private $dispatcher;

    public static function setUpBeforeClass() : void{
        try {
            self::$root = new TemporaryRootDirectory();
        } catch (\RuntimeException $exception){
            self::markTestSkipped($exception->getMessage());
        }
    }

    protected function setUp() : void{
        try {
            $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
            $core = $this->mockCore();
            $this->router = new Router($core);
        } catch (\RuntimeException $exception){
            $this->markTestSkipped($exception->getMessage());
        }
    }

    private function mockCore(){
        $core = $this->getMockBuilder(Core::class)->disableOriginalConstructor()->getMock();
        $core->method('getSetting')->willReturnMap([
            ['content_dir', null, self::$root->getRoot()]
            , ['base_url', null, 'http://test/']
            , ['content_ext', '.md', '.md']
        ]);
        $core->method('getService')->willReturnMap([
            [EventDispatcherInterface::class, $this->dispatcher]
        ]);

        return $core;
    }

    public function testUrlForPathAbsolute(){
        $page = 'sub/index.md';
        $expected = 'http://test/sub/';
        $result = $this->router->urlForPath($page);
        $this->assertEquals($expected, $result);
    }

    public function testUrlForPageRelative(){
        $page = 'sub/index.md';
        $expected = '/sub/';
        $result = $this->router->urlForPath($page, false);
        $this->assertEquals($expected, $result);
    }

    public function testMatchPrePostEvents(){
        $this->dispatcher->expects($this->atLeast(2))->method('dispatch')
            ->withConsecutive(
                [$this->equalTo(RoutingEvent::BEFORE), $this->isInstanceOf(RoutingEvent::class)]
                , [$this->equalTo(RoutingEvent::AFTER), $this->isInstanceOf(RoutingEvent::class)]
            );
        $this->router->match('/');
    }

    public function testMatchRootIndex(){
        $result = $this->router->match('/');
        $this->assertEquals(self::$root->getRoot() . 'index.md', $result);
    }

    public function testMatchSubDirectoryNoSlash(){
        $this->assertEquals(self::$root->getRoot() . 'sub/index.md', $this->router->match('/sub'));
    }

    public function testMatchSubDirectoryWithSlash(){
        $this->assertEquals(self::$root->getRoot() . 'sub/index.md', $this->router->match('/sub/'));
    }

    public function testMatchSubSubDirectoryNoSlash(){
        $this->assertEquals(self::$root->getRoot() . 'sub/page/index.md', $this->router->match('/sub/page'));
    }

    public function testMatchSubSubDirectoryWithSlash(){
        $this->assertEquals(self::$root->getRoot() . 'sub/page/index.md', $this->router->match('/sub/page/'));
    }

    public function testMatchSubPage(){
        $this->assertEquals(self::$root->getRoot() . 'sub/page/test.md', $this->router->match('/sub/page/test.md'));
    }
}
