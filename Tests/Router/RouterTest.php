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
use Core\Request\Request;
use Core\Response\Response;
use Core\Router\Route;
use Core\Tests\Mocks\MockPaths;
use Core\Tests\Models\testModels\User;
use Core\Tests\Stubs\Middlewares\StubMiddleware;
use Core\Tests\Stubs\Middlewares\StubMiddleware2;

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

    protected function getRouter()
    {
        $application = new Application(MockPaths::$basePath);
        $router = $this->application->make(\Core\Router\Router::class, $application, 'Router');

        return $router;
    }

    public function setRoutes()
    {
        Router::get('/basic/func', function() {
            return 'func';
        });
        Router::get('/basic/{id:num}', function($payload) {
            return $payload['id'];
        });
        Router::post('/post/{id:num}', function($payload) {
            return $payload['id'];
        });
        Router::put('/put/{id:num}', function($payload) {
            return $payload['id'];
        });
        Router::delete('/delete/{id:num}', function($payload) {
            return $payload['id'];
        });
        Router::get('http://somedomain.com/somePath', function() {
            return 'somedomain';
        });
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
        $this->assertSame($values, $route->getRouteParameters());
    }

    /**
     * @covers \Core\Router\Router::handle
     */
    public function testRouterHandling()
    {
        $router = $this->getRouter();
        $router->get('/test/{id:num}/page/{pageId:num}', '\Core\Tests\Stubs\Controllers\StubController@testId');

        $response = $router->handle(Request::create('/test/12/page/10'));
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('pageId', $response);
        $this->assertSame(12, $response['id']);
        $this->assertSame(10, $response['pageId']);

        $router = $this->getRouter();
        $router->get('/testName/{id:num}/page/{pageId:num}', '\Core\Tests\Stubs\Controllers\StubController@testName');
        /** @var Response $response */
        $response = $router->handle(Request::create('/testName/12/page/10'));
        $this->assertInstanceOf('\Core\Response\Response', $response);
        $this->assertSame("{\"id\":12,\"pageId\":10}", $response->getContent());
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::getNextCallable
     * @covers \Core\Router\Router::getFunctionArgs
     * @covers \Core\Router\Router::makeController
     */
    public function testControllerSupportsTypeHinting()
    {
        $application = new Application(MockPaths::$basePath);
        $router = $this->application->make(\Core\Router\Router::class, $application, 'Router');
        $router->get('/test/{id:num}/page/{pageId:num}', '\Core\Tests\Stubs\Controllers\StubController@testTypeHint');
        $response = $router->handle(Request::create('/test/12/page/10'));
        $this->assertInternalType('array', $response);
        $this->assertInternalType('array', $response[0]);
        $this->assertInstanceOf('\\Core\\FileSystem\\FileSystem', $response[1]);
        $this->assertInstanceOf('\\Core\\Request\\Request', $response[2]);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::getNextCallable
     * @covers \Core\Router\Router::getFunctionArgs
     * @covers \Core\Router\Router::makeController
     * @covers \Core\Controllers\BaseController::setApplication
     */
    public function testControllerConstructSupportsTypeHinting()
    {
        $application = new Application(MockPaths::$basePath);
        $router = $this->application->make(\Core\Router\Router::class, $application, 'Router');
        $this->application->register('User', User::class);

        $router->get('/test/{id:num}/page/{pageId:num}', '\Core\Tests\Stubs\Controllers\StubController2@testControllerConstructArg');

        $response = $router->handle(Request::create('/test/12/page/10'));
        $this->assertInstanceOf(User::class, $response);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::getNextCallable
     * @covers \Core\Router\Router::getFunctionArgs
     * @covers \Core\Router\Router::makeController
     * @covers \Core\Controllers\BaseController::setApplication
     */
    public function testControllerConstructSupportsTypeHintingAndHasApp()
    {
        $application = new Application(MockPaths::$basePath);
        $router = $this->application->make(\Core\Router\Router::class, $application, 'Router');
        $this->application->register('User', User::class);

        $router->get('/test/{id:num}/page/{pageId:num}', '\Core\Tests\Stubs\Controllers\StubController2@testControllerConstructApp');

        $response = $router->handle(Request::create('/test/12/page/10'));
        $this->assertInstanceOf(Application::class, $response);
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

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Router::post
     * @covers \Core\Router\Router::put
     * @covers \Core\Router\Router::delete
     */
    public function testRouteHandlingAsClosure()
    {
        $router = $this->getRouter();
        $this->setRoutes();
        $response = $router->handle(Request::create('http://example.com/basic/func'));
        $this->assertSame('func', $response);

        $router = $this->getRouter();
        $this->setRoutes();
        $response = $router->handle(Request::create('http://example.com/basic/10'));
        $this->assertSame(10, $response);

        $router = $this->getRouter();
        $this->setRoutes();
        $response = $router->handle(Request::create('http://example.com/post/12', 'POST'));
        $this->assertSame(12, $response);

        $router = $this->getRouter();
        $this->setRoutes();
        $response = $router->handle(Request::create('http://example.com/put/14', 'PUT'));
        $this->assertSame(14, $response);

        $router = $this->getRouter();
        $this->setRoutes();
        $response = $router->handle(Request::create('http://example.com/delete/16', 'DELETE'));
        $this->assertSame(16, $response);

        //404s
        $router = $this->getRouter();
        $this->setRoutes();
        $response3 = $router->handle(Request::create('http://example.com/put/14'));
        /** @var Response $response3 */
        $this->assertInstanceOf('\Core\Response\Response', $response3);
        $this->assertSame(404, $response3->getStatusCode());

        $router = $this->getRouter();
        $this->setRoutes();
        $response2 = $router->handle(Request::create('http://somedomain.com/somePath'));
        /** @var Response $response2 */
        $this->assertInstanceOf('\Core\Response\Response', $response2);
        $this->assertSame(404, $response2->getStatusCode());
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Router::post
     * @covers \Core\Router\Router::put
     * @covers \Core\Router\Router::delete
     * @covers \Core\Router\Router::makeController
     * @covers \Core\Router\Router::runController
     */
    public function testRouteHandlingAsClass()
    {
        $router = $this->getRouter();
        $router->get('/test/foo', '\Core\Tests\Stubs\Controllers\StubController@index');

        $response = $router->handle(Request::create('http://example.com/test/foo'));
        $this->assertSame('stubController::index', $response);

        $router = $this->getRouter();
        $router->post('/test/{id:num}', '\Core\Tests\Stubs\Controllers\StubController@returnable');
        $response = $router->handle(Request::create('http://example.com/test/10', 'POST'));
        $this->assertSame(10, $response);

        $router = $this->getRouter();
        $router->put('/test/{id:num}', '\Core\Tests\Stubs\Controllers\StubController@returnable');
        $response = $router->handle(Request::create('http://example.com/test/10', 'PUT'));
        $this->assertSame(10, $response);

        $router = $this->getRouter();
        $router->delete('/test/{id:num}', '\Core\Tests\Stubs\Controllers\StubController@returnable');
        $response = $router->handle(Request::create('http://example.com/test/10', 'DELETE'));
        $this->assertSame(10, $response);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Route::getMiddlewares
     * @covers \Core\Router\Route::executeMiddleware
     *
     */
    public function testMiddlewareAsClosure()
    {
        $router = $this->getRouter();
        $middleware = function($router, $next) {
            return 'middleware';
        };

        $router->get('/foo', function () {
            return 'hello world';
        }, ['middleware' => $middleware]);

        $response = $router->handle(Request::create('http://example.com/foo'));
        $this->assertSame('middleware', $response);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Route::setOptions
     * @covers \Core\Router\Route::setMiddleware
     * @covers \Core\Router\Route::getMiddlewares
     * @covers \Core\Router\Route::executeMiddleware
     *
     */
    public function testMiddlewareAsClass()
    {
        $router = $this->getRouter();

        $router->get('/foo', function () {
            return 'hello world';
        }, ['middleware' => StubMiddleware::class]);

        $response = $router->handle(Request::create('http://example.com/foo'));
        $this->assertSame('stubMiddleware', $response);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Route::setOptions
     * @covers \Core\Router\Route::setMiddlewares
     * @covers \Core\Router\Route::getMiddlewares
     * @covers \Core\Router\Route::executeMiddleware
     */
    public function testMiddlewareClassArray()
    {
        $router = $this->getRouter();

        $router->get('/foo', function () {
            return 'hello world';
        }, ['middlewares' => [StubMiddleware::class, StubMiddleware2::class]]);

        $response = $router->handle(Request::create('http://example.com/foo'));
        $this->assertSame('stubMiddleware2', $response);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Router::addRoute
     */
    public function testRoutePrefix()
    {
        $router = $this->getRouter();

        $router->get('/foo', function() {
            return 'hello world';
        }, ['prefix' => 'bar']);

        $response = $router->handle(Request::create('http://example.com/bar/foo'));
        $this->assertSame('hello world', $response);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Router::addRoute
     */
    public function testRoutePrefixSlashMakesNoDifference()
    {
        $router = $this->getRouter();

        $router->get('/foo', function() {
            return 'hello world';
        }, ['prefix' => '/bar']);

        $response2 = $router->handle(Request::create('http://example.com/bar/foo'));
        $this->assertSame('hello world', $response2);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Router::addRoute
     */
    public function testRoutePrefixDoesNotResolveOriginalUrl()
    {
        $router = $this->getRouter();

        $router->get('/foo', function() {
            return 'hello world';
        }, ['prefix' => 'bar']);

        $response = $router->handle(Request::create('http://example.com/foo'));
        /** @var Response $response */
        $this->assertInstanceOf('\Core\Response\Response', $response);
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Router::post
     * @covers \Core\Router\Router::put
     * @covers \Core\Router\Router::delete
     * @covers \Core\Router\Router::makeController
     * @covers \Core\Router\Router::runController
     * @covers \Core\Controllers\BaseController::getRouteData
     */
    public function testRouterDataIsAvailableInController()
    {
        $routeData = ['someKey' => 'someValue'];
        $router = $this->getRouter();
        $router->get('/foo/bar', '\Core\Tests\Stubs\Controllers\StubController@returnRouteData', ['data' => $routeData]);
        $response = $router->handle(Request::create('http://example.com/foo/bar'));
        $this->assertInstanceOf('\Core\Reactor\DataCollection', $response);
        $this->assertSame($routeData, $response->get());
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Route::parseUri
     */
    public function testRouteParameterDefinition()
    {
        $router = $this->getRouter();
        $router->get('/page/{id}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/bar'));
        $this->assertInternalType('string', $response);
        $this->assertSame('bar', $response);

        $router = $this->getRouter();
        $router->get('/page/{id}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/1'));
        $this->assertInternalType('string', $response);
        $this->assertSame('1', $response);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Route::parseUri
     */
    public function testRouteParameterOptionsDefinition()
    {
        $router = $this->getRouter();
        $router->get('/page/{id:num}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/1'));
        $this->assertInternalType('integer', $response);
        $this->assertSame(1, $response);

        $router = $this->getRouter();
        $router->get('/page/{id:alpha}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/foo'));
        $this->assertInternalType('string', $response);
        $this->assertSame('foo', $response);

        // 404's
        $router = $this->getRouter();
        $router->get('/page/{id:num}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/foo'));
        $this->assertInstanceOf('\Core\Response\Response', $response);
        $this->assertSame(404, $response->getStatusCode());

        $router = $this->getRouter();
        $router->get('/page/{id:alpha}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/10'));
        $this->assertInstanceOf('\Core\Response\Response', $response);
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Route::parseUri
     * @covers \Core\Router\Route::isOptional
     * @covers \Core\Router\Route::isInteger
     */
    public function testRouteParameterAsOptional()
    {
        $router = $this->getRouter();
        $router->get('/page/{id:?}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/'));
        $this->assertSame(null, $response);

        $router = $this->getRouter();
        $router->get('/page/{id:default=1}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/'));
        $this->assertSame('1', $response);

        $router = $this->getRouter();
        $router->get('/page/{id:num:default=10}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/'));
        $this->assertSame(10, $response);
    }

    /**
     * @covers \Core\Router\Router::handle
     * @covers \Core\Router\Router::parse
     * @covers \Core\Router\Router::run
     * @covers \Core\Router\Router::get
     * @covers \Core\Router\Route::parseUri
     * @covers \Core\Router\Route::isOptional
     * @covers \Core\Router\Route::isInteger
     */
    public function testRouterParametersSupportShortOptions()
    {
        $router = $this->getRouter();
        $router->get('/page/{id:i:default=10}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/'));
        $this->assertSame(10, $response);

        $router = $this->getRouter();
        $router->get('/page/{id:a:default=10}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/'));
        $this->assertSame('10', $response);
    }

    /**
     * @covers \Core\Router\Route::getCurrentRoute
     */
    public function testCurrentRouteMethod()
    {
        $router = $this->getRouter();
        $router->get('/page/{id:i:default=10}', function($payload){
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/'));
        $route = $router->getCurrentRoute();

        $this->assertInstanceOf('\Core\Router\Route', $route);
    }

    /**
     * @covers \Core\Router\Router::any
     */
    public function testAnyMethod()
    {
        // GET
        $router = $this->getRouter();
        $router->any('/page/{id}', function ($payload) {
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/somePage'));
        $this->assertSame('somePage', $response);

        // POST
        $router = $this->getRouter();
        $router->any('/page/{id}', function ($payload) {
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/somePage', 'POST'));
        $this->assertSame('somePage', $response);

        // PUT
        $router = $this->getRouter();
        $router->any('/page/{id}', function ($payload) {
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/somePage', 'PUT'));
        $this->assertSame('somePage', $response);

        // PATCH
        $router = $this->getRouter();
        $router->any('/page/{id}', function ($payload) {
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/somePage', 'PATCH'));
        $this->assertSame('somePage', $response);

        // DELETE
        $router = $this->getRouter();
        $router->any('/page/{id}', function ($payload) {
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/somePage', 'DELETE'));
        $this->assertSame('somePage', $response);

        // OPTIONS
        $router = $this->getRouter();
        $router->any('/page/{id}', function ($payload) {
            return $payload['id'];
        });
        $response = $router->handle(Request::create('http://example.com/page/somePage', 'OPTIONS'));
        $this->assertSame('somePage', $response);
    }
}
