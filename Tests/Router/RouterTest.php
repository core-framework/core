<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This file is part of the Core Framework package.
 *
 * (c) Shalom Sam <shalom.s@coreframework.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Tests\Router;

use Core\Application\Application;
use Core\Container\Container;
use Core\Facades\Router;
use Core\Response\Response;
use Core\Router\Route;
use Core\Tests\Mocks\MockPaths;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    public $application;

    public $router;

    public function setUp()
    {
        MockPaths::createMockPaths();
        $this->application = new Application(MockPaths::$basePath);
        $this->router = $this->application->make(\Core\Router\Router::class, $this->application, 'Router');
        //$service = $this->application->register('Router', \Core\Router\Router::class);
        //$service->setArguments([$this->application]);
        $this->setRoutes();
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    // ----- Helpers -----

    public function setRoutes()
    {
        Router::get('/test/{id:default=1}', 'testController@testId');
        Router::get('/test/{fname}/{id:num:default=1}', 'testController@testName');
        //grouping
        Router::group(['prefix' => '/group'], function() {
            Router::get('/groupTest/{id:default=1}', 'testController@testId');
        });
    }

    /**
     * @return array
     */
    public function pathProvider()
    {
        return [
            ['/test/1', ['id'], ['id' => '1'], 'testController@testId'],
            ['/test/', ['id'], ['id' => '1'], 'testController@testId'],
            ['/test/2', ['id'], ['id' => '2'], 'testController@testId'],
            ['/test/sam/3', ['fname','id'], ['fname' => 'sam', 'id' => 3], 'testController@testName'],
            ['/test/sham/4', ['fname','id'], ['fname' => 'sham', 'id' => 4], 'testController@testName'],
            ['/group/groupTest/1', ['id'], ['id' => '1'], 'testController@testId'],
        ];
    }

    public function getRequestMock($path)
    {
        $request = $this->getMockBuilder('\Core\Request\Request')
            ->setConstructorArgs(array($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES))
            ->setMethods(array('getHttpMethod', 'getPath'))
            ->getMock();

        $request->expects($this->once())
            ->method('getHttpMethod')
            ->will($this->returnValue('GET'));

        $request->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($path));

        return $request;
    }

    public function getRouterMock($controller = null)
    {
        $router = $this->getMockBuilder('\Core\Router\Router')->setConstructorArgs([$this->application])->setMethods(array('makeController'))->getMock();
        $router->expects($this->any())
            ->method("makeController")
            ->will($this->returnValue($controller));

        return $router;
    }

    public function getControllerStub($class, $method, $values)
    {
        $controller = $this->getMockBuilder($class)->setMethods(array($method))->getMock();
        $controller->expects($this->any())
            ->method($method)
            ->with($this->equalTo($values))
            ->will($this->returnCallback(array($this, 'responseCallback')));

        return $controller;
    }

    public function getClass($action)
    {
        $class = explode('@', $action)[0];
        return 'app\\Controllers\\' . $class;
    }

    public function getMethod($action)
    {
        return explode('@', $action)[1];
    }

    public function getResponseContent($valuesString)
    {
        return "<html><h1>Hello World - {$valuesString} </h1></html>";
    }

    public function responseCallback($payload)
    {
        $valuesString = serialize($payload);
        $response = new Response("<html><h1>Hello World - {$valuesString} </h1></html>", 200);
        return $response;
    }

    // ----- TESTS ------

    /**
     * Simple Router Test
     */
    public function testRouter()
    {
        $this->router = $this->application->get('Router');
        $this->assertInstanceOf('\Core\Router\Router', $this->router);
    }

    /**
     * @param $path
     * @param $params
     * @param $values
     * @param $action
     * @dataProvider pathProvider
     */
    public function testRouterRouting($path, $params, $values, $action)
    {
        $request = $this->getRequestMock($path);

        /** @var Route $route */
        $route = $this->router->parse($request);
        $this->assertInstanceOf('\Core\Router\Route', $route);
        $this->assertSame($action, $route->getAction());
        $this->assertSame($params, $route->getParameterNames());
        $this->assertSame($values, $route->getParameterValues());
    }

    /**
     * @param $path
     * @param $params
     * @param $values
     * @param $action
     * @dataProvider pathProvider
     */
    public function testRouterHandling($path, $params, $values, $action)
    {
        $valuesString = serialize($values);
        $method = $this->getMethod($action);
        $class = $this->getClass($action);
        
        $controller = $this->getControllerStub($class, $method, $values);
        $router = $this->getRouterMock($controller);
        $this->application->updateInstance('Router', $router);
        $request = $this->getRequestMock($path);

        $this->setRoutes();

        /** @var Response $response */
        $response = $router->handle($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame($this->getResponseContent($valuesString), $response->getContent());
    }

    /**
     * @runInSeparateProcess
     *
     * Running in a separate process ensures that the controller classes don't exist
     * thus causing the 604 "Controller not Found" error
     * Note: Controller is not passed/defined in getRouterMock
     */
    public function testRouterAlwaysReturnsResponse()
    {
        $router = $this->getRouterMock();
        $this->application->updateInstance('Router', $router);
        $request = $this->getRequestMock('/test/2');
        $this->setRoutes();

        /*
         * Following matches route '/test/{id:default=1}' but since controller
         * is not defined we get a "Controller not found" (604) error
         */
        $response = $router->handle($request);
        $this->assertInstanceOf('\\Core\\Response\\Response', $response);
        $this->assertEquals(604, $response->getStatusCode());

        /*
         * Following does not match any route (hence response is a 404)
         */
        $response = $router->handle($request = $this->getRequestMock('/asdasd/2'));
        $this->assertInstanceOf('\\Core\\Response\\Response', $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
