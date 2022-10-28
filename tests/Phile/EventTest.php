<?php

namespace Phile\Test;


use Composer\EventDispatcher\EventSubscriberInterface;
use Phile\Core;
use Phile\Core\ServiceLocator;
use Phile\Event\CoreEvent;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * the EventTest class
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package PhileTest
 */
class EventTest extends TestCase {
    /** @var EventDispatcherInterface */
    private $dispatcher;

    protected function setUp() : void{
        Core::bootstrap(['themes_dir' => __DIR__, 'theme' => '']);
        $this->dispatcher = ServiceLocator::getService('Phile_EventDispatcher');
    }

    private function addListener() : MockObject{
        $handler = $this->getMockBuilder(\stdClass::class)->addMethods(['__invoke'])->getMock();
        $this->dispatcher->addListener(CoreEvent::LOADED, $handler);

        return $handler;
    }

    public function testCanAddAndRemoveListener(){
        $this->assertFalse($this->dispatcher->hasListeners(CoreEvent::LOADED));
        $handler = $this->addListener();
        $this->assertTrue($this->dispatcher->hasListeners(CoreEvent::LOADED));
        $this->dispatcher->removeListener(CoreEvent::LOADED, $handler);
        $this->assertFalse($this->dispatcher->hasListeners(CoreEvent::LOADED));
    }

    public function testDispatchesEvent(){
        $handler = $this->addListener();
        $handler->expects($this->once())->method('__invoke')->with($this->isInstanceOf(Event::class));

        $this->dispatcher->dispatch(CoreEvent::LOADED, new CoreEvent());
    }
}
