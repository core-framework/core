<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 13/10/15
 * Time: 11:27 AM
 */

namespace Core\Tests\Config;

use Core\Config\Config;
use org\bovigo\vfs\vfsStream;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $config Config
     */
    public $config;
    public static $testConf;
    public static $testConfOverrideArr = [
        'testKey' => ['someChildKey' => 'overrideValue'],
        'testing' => 'developer'
    ];
    public static $testConfArr = [
        'testKey' => ['someChildKey' => 'someChildValue'],
        'testing' => 'tester'
    ];
    public static $frameworkConfArr = [
        '$db' => [
            'type' => 'mysql',
            'db' => 'coreframework_db',
            'host' => '127.0.0.1',
            'user' => 'root',
            'pass' => 'pass'
        ],
        '$global' => [],
        '$routes' => []
    ];
    public static $frameworkConf;

    public static $root;

    public function setUp()
    {
        static::$root = vfsStream::setup('config', 0777);
        vfsStream::newFile('testFile.conf.php', 0777)->at(static::$root);
        static::$testConf = vfsStream::url('config/testFile.conf.php');
        static::$frameworkConf = vfsStream::url('config/framework.conf.php');
        static::_initConfFiles();
        $this->config = new Config(vfsStream::url('config'));
    }

    public static function _initConfFiles()
    {
        $data1 = '<?php return ' . var_export(static::$testConfArr, true) . ";\n ?>";
        file_put_contents(static::$testConf, $data1);
        $data2 = '<?php return ' . var_export(static::$frameworkConfArr, true) . ";\n ?>";
        file_put_contents(static::$frameworkConf, $data2);
    }

    public function tearDown()
    {

    }

    /**
     * @covers \Core\Config\Config::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnCreation()
    {
        new Config([]);
    }

    /**
     * @covers \Core\Config\Config::add
     */
    public function testAddConf()
    {
        Config::add('addArr', array('testKey' => 'testVal'));
        $this->assertArrayHasKey('addArr', Config::$allConf);
    }

    /**
     * @covers \Core\Config\Config::set
     */
    public function testSetConf()
    {
        Config::set(array('newArr' => array('testKey' => 'testVal')));
        $this->assertArrayHasKey('newArr', Config::$allConf);
        $this->assertArrayNotHasKey('addArr', Config::$allConf);
    }

    /**
     * @covers \Core\Config\Config::get
     */
    public function testConfigGet()
    {
        $content = Config::get('$db.host');
        $this->assertSame('127.0.0.1', $content);
    }

    /**
     * @covers \Core\Config\Config::get
     */
    public function testConfigGetAsSearch()
    {
        $content = Config::get('user');
        $this->assertSame('root', $content);
    }

    /**
     * @covers \Core\Config\Config::get
     */
    public function testConfigGetFromFile()
    {
        $content = Config::get('testFile:testKey.someChildKey');
        $this->assertSame('someChildValue', $content);
    }

    /**
     * @covers \Core\Config\Config::get
     * @runInSeparateProcess
    */
    public function testConfigGetOverride()
    {
        $env = Config::getEnvironment();
        $dir = vfsStream::newDirectory($env, 0777)->at(static::$root);
        vfsStream::newFile('testFile.conf.php', 0777)->at($dir);
        $overrideConf = vfsStream::url('config/'.$env.'/testFile.conf.php');
        $data2 = '<?php return ' . var_export(static::$testConfOverrideArr, true) . ";\n ?>";
        file_put_contents($overrideConf, $data2);

        $content = Config::get('testFile:testKey.someChildKey');
        $this->assertSame('overrideValue', $content);
    }
}