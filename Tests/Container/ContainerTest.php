<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 22/11/14
 * Time: 8:47 AM
 */

namespace Core\Tests\Container;

use Core\Config\Config;
use Core\Container\Container;
use Core\View\View;
use org\bovigo\vfs\vfsStream;

class ContainerTest extends \PHPUnit_Framework_TestCase {

    public $config;

    public static $root;
    public static $viewConf;
    public static $basePath;
    public static $frameworkConf;

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

    public function setUp()
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
        $data2 = '<?php return ' . var_export([], true) . ";\n ?>";
        file_put_contents(static::$frameworkConf, $data2);
        $data3 = '<?php return ' . var_export([], true) . ";\n ?>";
        file_put_contents(static::$viewConf, $data3);
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    public function getMockApplication()
    {
        $application = $this->getMockBuilder('\\Core\\Application\\Application')
            ->setMethods(array('getConfigInstance'))
            ->getMock();

        $path = vfsStream::url('root/config');
        $config = new Config($path);

        $application->expects($this->any())
            ->method('getConfigInstance')
            ->will($this->returnValue($config));

        return $application;
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::get
     * @throws \ErrorException
     */
    public function testReferenceMatch()
    {
        $Container = new Container();
        $Container->register('_Container', $Container);
        $Container->register('Smarty', '\\Smarty');
        $Container->register('Application', $this->getMockApplication());
        $Container->register('View', '\\Core\\View\\View')->setArguments(array('Application'));

        /** @var View $a */
        $a = $Container->get('View');
        $a->setShowHeader(true);
        /** @var View $b */
        $b = $Container->get('View');
        $b->setShowFooter(true);
        $c = $Container->get('View');

        $this->assertEquals($a, $c);
        $this->assertEquals($b, $c);
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::get
     * @throws \ErrorException
     */
    public function testCanRegisterClass()
    {
        $Container = new Container();
        $Container->register('Cache', \Core\Cache\OPCache::class);

        $cache = $Container->get('Cache');

        $this->assertInstanceOf('\\Core\\Cache\\OPCache', $cache);
    }

} 
