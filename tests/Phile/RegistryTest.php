<?php

namespace Phile\Test;

use Phile\Core\Registry;
use PHPUnit\Framework\TestCase;

/**
 * the RegistryTest class
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package PhileTest
 */
class RegistryTest extends TestCase {
    /**
     * @var Registry
     */
    protected $registry;

    protected function setUp() : void{
        $this->registry = Registry::getInstance();
    }

    public function testValueCanSetToRegistry(){
        $this->registry->set('test', 'test-value');
        $this->assertEquals('test-value', $this->registry->get('test'));
    }

    public function testGettingInstance(){
        $this->assertInstanceOf('\Phile\Core\Registry', $this->registry);
    }

    public function testValueIsRegistered(){
        $this->registry->set('testValueIsRegistered', 'testValueIsRegistered');
        $this->assertEquals(
            true,
            $this->registry->isRegistered(
                'testValueIsRegistered'
            )
        );
    }

    public function testValueIsNotRegistered(){
        $this->assertEquals(
            false,
            $this->registry->isRegistered(
                'testValueIsNotRegistered'
            )
        );
    }
}
