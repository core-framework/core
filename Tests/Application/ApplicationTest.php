<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 02/11/15
 * Time: 1:25 AM
 */

namespace Core\Tests\Application;

use Core\Application\Application;
use Core\Cache\AppCache;
use Core\Config\Config;
use Core\Container\Container;
use Core\Response\Response;
use Core\Router\Router;
use org\bovigo\vfs\vfsStream;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    public $app;
    public static $basePath;
    public static $frameworkConf;
    public static $viewConf;
    public static $frameworkConfArr = [
        '$db' => [],
        '$global' => [
            'templateEngine' => 'Smarty'
        ],
        '$routes' => [
            '/' => [
                'pageName' => 'test',
                'pageTitle' => 'Test',
                'httpMethod' => 'GET',
                'controller' => '\\Core\\Controllers:testController:helloWorldAction'
            ],
            '/hello/{name}' => [
                'pageName' => 'test',
                'pageTitle' => 'Test',
                'argReq' => ['name' => ':alpha'],
                'argDefault' => 'name',
                'httpMethod' => 'GET',
                'controller' => '\\Core\\Controllers:testController::helloAction'
            ],
        ],
        '$env' => [],
        '$services' => [

            'View' => [
                'definition' => \Core\View\View::class,
                'dependencies' => ['App']
            ],

            'Smarty' => \Smarty::class
        ]
    ];
    public static $structure = [
        'storage' => [
            'framework' => [
                'cache' => [
                    'emptyFile.php' => ""
                ]
            ],
            'smarty_cache' => [
                'cache' => [],
                'config' => [],
                'configs' => [],
                'templates_c' => []
            ]
        ],
        'config' => [
            'framework.conf.php' => "",
            'view.conf.php' => ""
        ]
    ];

    public function _createMockPaths()
    {
        vfsStream::setup('root', 0777, self::$structure);
        static::$frameworkConf = vfsStream::url('root/config/framework.conf.php');
        static::$viewConf = vfsStream::url('root/config/view.conf.php');
        static::$basePath = vfsStream::url('root');
        static::setFilePermissions();
        static::_initConfFiles();
    }

    public static function setFilePermissions()
    {
        chmod(vfsStream::url('root/storage/framework/cache'), 0777);
        chmod(vfsStream::url('root/storage/smarty_cache/'), 0777);
        chmod(vfsStream::url('root/storage/smarty_cache/cache'), 0777);
        chmod(vfsStream::url('root/storage/smarty_cache/templates_c/'), 0777);
    }

    public static function _initConfFiles()
    {
        $data2 = '<?php return ' . var_export(static::$frameworkConfArr, true) . ";\n ?>";
        file_put_contents(static::$frameworkConf, $data2);
        $data3 = '<?php return ' . var_export([], true) . ";\n ?>";
        file_put_contents(static::$viewConf, $data3);

    }

    public function setUp()
    {
        $this->_createMockPaths();
        
        //$this->app = new Application(self::$basePath);
        $path = vfsStream::url('root/config/');

        $config = new Config($path);

        $app = $this->getMockBuilder('\\Core\\Application\\Application')
            ->setConstructorArgs(array(self::$basePath))
            ->setMethods(array('getConfigInstance'))
            ->getMock();

        $app->expects($this->any())->method('getConfigInstance')
            ->will($this->returnValue($config));

        $this->app = $app;
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        AppCache::reset();
        parent::tearDown();
    }

    public function setRoute()
    {
        Router::get('/', 'testController@helloWorld');
    }

    public function getRequestMock($path = "/")
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

    public function getRouterMock($class = '\\app\\Controllers\\testController', $method = 'helloWorld')
    {
        $controller = $this->getMockBuilder($class)->setMethods(array($method))->getMock();
        $controller->expects($this->any())
            ->method($method)
            ->will($this->returnCallback(array($this, 'responseCallback')));
        
        $router = $this->getMockBuilder('\Core\Router\Router')->setMethods(array('makeController'))->getMock();
        $router->expects($this->any())
            ->method("makeController")
            ->will($this->returnValue($controller));

        return $router;
    }

    public function getApplicationMock($basePath = null)
    {

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
     * @covers \Core\Application\BaseApplication::loadBaseComponents
     */
    public function testIfBaseComponentsAreLoaded()
    {
        $this->assertInstanceOf('\\Core\\Router\\Router', $this->app->getRouter());
        $this->assertInstanceOf('\\Core\\Cache\\AppCache', $this->app->getCache());
    }

    /**
     * @covers \Core\Application\BaseApplication::clearCacheIfRequired
     * @throws \ErrorException
     */
    public function testIfCacheIsClearWhenSetInRouter()
    {
        AppCache::cacheContent('testCache', "testContent", 0);

        // mimic real world GET request
        $_GET['action'] = "clear_cache";
        $app = new Application(self::$basePath);

        $this->assertFalse($app->getCache()->getCache('testCache'));
    }

    /**
     * @covers \Core\Application\BaseApplication::loadConfig
     */
    public function testIfApplicationHasConfig()
    {
        $this->assertArrayHasKey('$db', $this->app->configArr);
        $this->assertArrayHasKey('$global', $this->app->configArr);
        $this->assertArrayHasKey('$routes', $this->app->configArr);
    }

    /**
     * @covers \Core\Application\BaseApplication::setEnvironment
     */
    public function testIfEnvironmentIsDevelopment()
    {
        $this->assertSame(Application::DEVELOPMENT_STATE, $this->app->environment());
    }

    /**
     * @covers \Core\Application\BaseApplication::setEnvironment
     */
    public function testDisplayErrorsIsSet()
    {
        $this->assertSame('On', ini_get('display_errors'));
    }

    /**
     * @covers \Core\Application\BaseApplication::setEnvironment
     */
    public function testErrorReportingIsSet()
    {
        $this->assertSame(error_reporting(), E_ALL);
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
        $this->app->setRouter($this->getRouterMock());
        $this->setRoute();
        $this->app->run();
        $this->assertTrue(headers_sent());
        $this->expectOutputString("<html><h1>Hello World</h1></html>");
    }

}
