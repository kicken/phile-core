<?php

namespace Phile\Test;

use Phile\Core;
use Phile\Core\ServiceLocator;
use Phile\ServiceLocator\ErrorHandlerInterface;
use Phile\ServiceLocator\MetaParserInterface;
use Phile\ServiceLocator\ParserInterface;
use Phile\ServiceLocator\PersistenceInterface;
use PHPUnit\Framework\TestCase;

/**
 * the ServiceLocatorTest class
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package PhileTest
 */
class ServiceLocatorTest extends TestCase {
    protected function setUp() : void{
        Core::bootstrap(['themes_dir' => __DIR__, 'theme' => '']);
    }

    public function testServicePhileCacheExists(){
        $this->assertEquals(
            false,
            ServiceLocator::hasService(
                'Phile_Cache'
            )
        );
    }

    public function testServicePhileTemplateExists(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_Template'
            )
        );
    }

    public function testServicePhileParserExists(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_Parser'
            )
        );
    }

    public function testServicePhileDatePersistenceExists(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_Data_Persistence'
            )
        );
    }

    public function testServicePhileParserMetaExists(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_Parser_Meta'
            )
        );
    }

    public function testServicePhileErrorHandlerExists(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_ErrorHandler'
            )
        );
    }

    public function testServicePhileTemplateExistsAndHasCorrectInstance(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_Template'
            )
        );
        $this->assertInstanceOf(
            '\Phile\ServiceLocator\TemplateInterface',
            ServiceLocator::getService(
                'Phile_Template'
            )
        );
    }

    public function testServicePhileParserExistsAndHasCorrectInstance(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_Parser'
            )
        );
        $this->assertInstanceOf(
            ParserInterface::class,
            ServiceLocator::getService(
                'Phile_Parser'
            )
        );
    }

    public function testServicePhileDatePersistenceExistsAndHasCorrectInstance(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_Data_Persistence'
            )
        );
        $this->assertInstanceOf(
            PersistenceInterface::class,
            ServiceLocator::getService(
                'Phile_Data_Persistence'
            )
        );
    }

    public function testServicePhileParserMetaExistsAndHasCorrectInstance(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_Parser_Meta'
            )
        );
        $this->assertInstanceOf(
            MetaParserInterface::class,
            ServiceLocator::getService(
                'Phile_Parser_Meta'
            )
        );
    }

    public function testServicePhileErrorHandlerExistsAndHasCorrectInstance(){
        $this->assertEquals(
            true,
            ServiceLocator::hasService(
                'Phile_ErrorHandler'
            )
        );
        $this->assertInstanceOf(
            ErrorHandlerInterface::class,
            ServiceLocator::getService(
                'Phile_ErrorHandler'
            )
        );
    }
}
