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
        $this->response = $this->getMockBuilder(Response::class)->getMock();
    }

    protected function tearDown() : void{
        unset($this->response);
    }

    public function testRedirect(){
        $location = 'foo';

        $this->response->expects($this->once())
            ->method('setStatusCode')
            ->with('301')
            ->will($this->returnSelf());
        $this->response->expects($this->once())
            ->method('setHeader')
            ->with('Location', $location, true)
            ->will($this->returnSelf());

        $this->response->redirect($location, 301);
    }
}
