<?php

namespace Phile\Test\Core;

use Phile\Core\Response;
use PHPUnit\Framework\TestCase;

/**
 * the ResponseTest class
 *
 * @author  PhileCms
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package PhileTest
 */
class ResponseTest extends TestCase {
    private $response;

    protected function setUp() : void{
        parent::setUp();
        $this->response = new Response();
    }

    protected function tearDown() : void{
        unset($this->response);
    }

    public function testRedirect(){
        $location = 'foo';

        $this->response->redirect($location, 301);
        $this->assertEquals(301, $this->response->getStatusCode());
        $this->assertEquals(['Location' => 'foo'], $this->response->getHeaders());
        $this->assertStringContainsString('<a href', $this->response->getBody());
    }
}
