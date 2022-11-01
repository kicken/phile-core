<?php

namespace Phile\Test;

use Phile\Core;
use Phile\Service\MetaParserInterface;
use Phile\Service\ParserInterface;
use Phile\Service\PersistenceInterface;
use Phile\Service\RouterInterface;
use Phile\Service\TemplateInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CoreTest extends TestCase {
    private $core;
    private static $root;

    public static function setUpBeforeClass() : void{
        self::$root = new TemporaryRootDirectory();
    }

    protected function setUp() : void{
        $this->core = new Core(['root_dir' => self::$root->getRoot()]);
    }

    public function testHasDefaultServices(){
        $this->assertTrue($this->core->hasService(EventDispatcherInterface::class));
        $this->assertTrue($this->core->hasService(MetaParserInterface::class));
        $this->assertTrue($this->core->hasService(ParserInterface::class));
        $this->assertTrue($this->core->hasService(PersistenceInterface::class));
        $this->assertTrue($this->core->hasService(RouterInterface::class));
        $this->assertTrue($this->core->hasService(TemplateInterface::class));
    }

    public function testGetSetting(){
        $this->assertEquals(self::$root->getRoot(), $this->core->getSetting('root_dir'));
    }
}
