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

namespace Core\Tests\Application;

use Core\Application\Application;
use Core\Container\Container;
use Core\Facades\Router;
use Core\Response\Response;
use Core\Tests\Mocks\MockPaths;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public $app;

    public function setUp()
    {
        MockPaths::createMockPaths();
        $this->app = new Application(MockPaths::$basePath);
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    public function setRoute()
    {
        Router::get('/', function () {
            return new Response("<html><h1>Hello World</h1></html>");
        });
    }

    public function getRequestMock($path = "/")
    {
        $request = $this->getMockBuilder('\\Core\\Request\\Request')
            ->setConstructorArgs(array($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES))
            ->setMethods(array('getHttpMethod', 'getPath'))
            ->getMock();

        $request->expects($this->any())
            ->method('getHttpMethod')
            ->will($this->returnValue('GET'));

        $request->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($path));

        return $request;
    }

    public function getRouterMock($class = '\\app\\Controllers\\testController', $method = 'helloWorld')
    {
        $controller = $this->getMockBuilder($class)->setMethods(array($method))->getMock();
        $controller->expects($this->any())
            ->method($method)
            ->will($this->returnCallback(array($this, 'responseCallback')));
        
        $router = $this->getMockBuilder('\\Core\\Router\\Router')->setConstructorArgs([$this->app])->setMethods(array('makeController'))->getMock();
        $router->expects($this->any())
            ->method("makeController")
            ->will($this->returnValue($controller));

        return $router;
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

    public function responseCallback()
    {
        $response = new Response("<html><h1>Hello World</h1></html>", 200);
        return $response;
    }



    // PRE RUN TESTS
    /**
     * @covers \Core\Application\BaseApplication::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('\\Core\\Application\\Application', $this->app);
    }

    /**
     * @covers \Core\Application\BaseApplication::registerApp
     */
    public function testIfAppIsRegistered()
    {
        $this->assertInstanceOf('\\Core\\Application\\Application', Application::$app);
        $this->assertInstanceOf('\\Core\\Application\\Application', Application::get('App'));
    }

    /**
     * @covers \Core\Application\BaseApplication::registerCoreComponents
     */
    public function testIfBaseComponentsAreLoaded()
    {
        $this->assertInstanceOf('\\Core\\Router\\Router', $this->app->getRouter());
        $this->assertInstanceOf('\\Core\\Cache\\FileCache', $this->app->getCache());
    }

    /**
     * @covers \Core\Application\BaseApplication::clearCacheIfRequired
     * @throws \ErrorException
     */
    public function testIfCacheIsClearWhenSetInRouter()
    {
        //AppCache::cacheContent('testCache', "testContent", 0);
        //Cache::put('testCache', "testContent", 0);

        // mimic real world GET request
        //$_GET['action'] = "clear_cache";
        //$app = new Application(MockPaths::$basePath);

        //$this->assertFalse($app->getCache()->get('testCache'));
    }

    /**
     * @covers \Core\Application\BaseApplication::loadConfig
     */
    public function testIfApplicationHasConfig()
    {
        $config = $this->app->getConfig()->all();
        $this->assertArrayHasKey('database', $config);
        $this->assertArrayHasKey('app', $config);
        $this->assertArrayHasKey('router', $config);
    }

    /**
     * @covers \Core\Application\BaseApplication::registerServicesFromConfig
     */
    public function testIfServicesFromConfigAreRegistered()
    {
        $this->assertInstanceOf('\\Core\\View\\View', $this->app->get('View'));
    }


    //****** POST RUN METHOD TESTS

    /**
     * @covers \Core\Application\BaseApplication::run
     */
    public function testIfRunProducesOutput()
    {
        MockPaths::createMockPaths();
        $this->app = new Application(MockPaths::$basePath);
        $this->app->setRequest($this->getRequestMock());
        $this->app->setRouter($this->getRouterMock());
        $this->setRoute();
        $this->app->run();
        $this->assertTrue(headers_sent());
        $this->expectOutputString("<html><h1>Hello World</h1></html>");
    }

}
