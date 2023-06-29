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
    private static TemporaryRootDirectory $root;
    private Router $router;
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
            ['content_dir', null, $this->getContentRoot()]
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
        $actual = $this->router->urlForPath($page);
        $this->assertEquals($expected, $actual);
    }

    public function testUrlForPathAbsoluteWithLeadingSlash(){
        $page = '/sub/index.md';
        $expected = 'http://test/sub/';
        $actual = $this->router->urlForPath($page);
        $this->assertEquals($expected, $actual);
    }

    public function testUrlForPageRelative(){
        $page = 'sub/index.md';
        $expected = '/sub/';
        $actual = $this->router->urlForPath($page, false);
        $this->assertEquals($expected, $actual);
    }

    public function testUrlForPageRelativeWithLeadingSlash(){
        $page = '/sub/index.md';
        $expected = '/sub/';
        $actual = $this->router->urlForPath($page, false);
        $this->assertEquals($expected, $actual);
    }

    public function testMatchPrePostEvents(){
        $this->dispatcher->expects($this->atLeast(2))->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(RoutingEvent::class), $this->equalTo(RoutingEvent::BEFORE)]
                , [$this->isInstanceOf(RoutingEvent::class), $this->equalTo(RoutingEvent::AFTER)]
            );
        $this->router->match('/');
    }

    public function testMatchRootIndex(){
        $actual = $this->router->match('/');
        $this->assertEquals($this->getExpectedContentPath('index.md'), $actual);
    }

    public function testMatchSubDirectoryNoSlash(){
        //Should return null because the request should be
        //redirected to the url with a trailing slash.
        $actual = $this->router->match('/sub');
        $this->assertNull($actual);
    }

    public function testMatchSubDirectoryWithSlash(){
        $actual = $this->router->match('/sub/');
        $this->assertEquals($this->getExpectedContentPath('sub/index.md'), $actual);
    }

    public function testMatchSubSubDirectoryNoSlash(){
        //Should return null because the request should be
        //redirected to the url with a trailing slash.
        $actual = $this->router->match('/sub/page');
        $this->assertNull($actual);
    }

    public function testMatchSubSubDirectoryWithSlash(){
        $actual = $this->router->match('/sub/page/');
        $this->assertEquals($this->getExpectedContentPath('sub/page/index.md'), $actual);
    }

    public function testMatchSubPage(){
        $actual = $this->router->match('/sub/page/test');
        $this->assertEquals($this->getExpectedContentPath('sub/page/test.md'), $actual);
    }

    private function getContentRoot() : string{
        return self::$root->getRoot() . DIRECTORY_SEPARATOR . 'content';
    }

    private function getExpectedContentPath(string $path) : string{
        return $this->getContentRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
