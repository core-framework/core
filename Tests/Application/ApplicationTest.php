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
use Core\Container\Container;
use org\bovigo\vfs\vfsStream;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    public $app;
    public static $basePath;
    public static $frameworkConf;
    public static $frameworkConfArr = [
        '$db' => [],
        '$global' => [],
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
                'definition' => \Core\Views\AppView::class,
                'dependencies' => [
                    '\\Core\\Application\\Application::getBasePath',
                    'Smarty'
                ]
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
            'framework.conf.php' => ""
        ]
    ];

    public function _createMockPaths()
    {
        vfsStream::setup('root', 0777, self::$structure);
        static::$frameworkConf = vfsStream::url('root/config/framework.conf.php');
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
    }

    public function setUp()
    {
        $this->_createMockPaths();
        $this->app = new Application(self::$basePath);
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        AppCache::reset();
        parent::tearDown();
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
        $this->assertInstanceOf('\\Core\\Router\\Router', $this->app->router);
        $this->assertInstanceOf('\\Core\\Cache\\AppCache', $this->app->cache);
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

        $this->assertFalse($app->cache->getCache('testCache'));
    }

    /**
     * @covers \Core\Application\BaseApplication::loadConfig
     */
    public function testIfApplicationHasConfig()
    {
        $this->assertArrayHasKey('$db', $this->app->config);
        $this->assertArrayHasKey('$global', $this->app->config);
        $this->assertArrayHasKey('$routes', $this->app->config);
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
        $this->assertInstanceOf('\\Core\\Views\\AppView', $this->app->get('View'));
    }

    /**
     * @covers \Core\Application\BaseApplication::setRouterConf
     */
    public function testIfRouterConfigIsSet()
    {
        $this->assertTrue($this->app->router->isConfigSet);
        $this->assertTrue(self::$frameworkConfArr === $this->app->router->config);
    }


    //****** POST RUN METHOD TESTS

    /**
     * @covers \Core\Application\BaseApplication::run
     */
    public function testIfRunProducesOutput()
    {
        $this->app->run();
        $this->assertTrue(headers_sent());
        $this->expectOutputString("hello world!");
    }

}
