<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 13/10/15
 * Time: 11:27 AM
 */

namespace Tests\Config;


use Core\Config\Config;
use org\bovigo\vfs\vfsStream;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $config Config
     */
    public $config;
    public static $editableConf;
    public static $frameworkConfArr = [
        '$db' => [],
        '$global' => [],
        '$routes' => []
    ];
    public static $frameworkConf;

    public function setUp()
    {
        $root = vfsStream::setup('config', 0777);
        vfsStream::newFile('override.conf.php', 0777)->at($root);
        static::$editableConf = vfsStream::url('config/override.conf.php');
        static::$frameworkConf = vfsStream::url('config/framework.conf.php');
        static::_initConfFiles();
        $this->config = new Config();
    }

    public static function _initConfFiles()
    {
        $data1 = '<?php return ' . var_export(array(), true) . ";\n ?>";
        file_put_contents(static::$editableConf, $data1);
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
        new Config('');
    }

    /**
     * @covers \Core\Config\Config::__construct
     */
    public function testSetUpUsingArray()
    {
        $config = new Config(array('Test' => array('testArr')));
        $this->assertInstanceOf('\\Core\\Config\\Config', $config);
        $this->assertArrayHasKey('Test', Config::$allConf);
    }

    /**
     * @covers \Core\Config\Config::addConf
     */
    public function testAddConf()
    {
        Config::addConf('addArr', array('testKey' => 'testVal'));
        $this->assertArrayHasKey('addArr', Config::$allConf);
    }

    /**
     * @covers \Core\Config\Config::setConf
     */
    public function testSetConf()
    {
        Config::setConf(array('newArr' => array('testKey' => 'testVal')));
        $this->assertArrayHasKey('newArr', Config::$allConf);
        $this->assertArrayNotHasKey('addArr', Config::$allConf);
    }

    /**
     * @covers \Core\Config\Config::setFile
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Given argument must be a readable file path.
     */
    public function testExceptionOnSetFile()
    {
        Config::setFile("");
    }

    /**
     * @covers \Core\Config\Config::setFile
     */
    public function testSetFile()
    {
        Config::setFile(static::$frameworkConf);
        $this->assertArrayHasKey('$global', Config::$allConf);
        $this->assertArrayHasKey('$db', Config::$allConf);
        $this->assertArrayHasKey('$routes', Config::$allConf);
    }

    /**
     * @covers \Core\Config\Config::setEditableConfFile
     */
    public function testSetEditableConfFile()
    {
        $editablePath = static::$editableConf;
        $this->config->setEditableConfFile($editablePath);
        $this->assertEquals($editablePath, Config::$confEditablePath);
    }

    /**
     * @covers \Core\Config\Config::store
     * @covers \Core\Config\Config::getFileContent
     */
    public function testStore()
    {
        Config::store(['storedArr' => 'storedVal']);
        $this->assertFileExists(Config::$confEditablePath);

        $contents = Config::getFileContent(Config::$confEditablePath);
        $this->assertArrayHasKey('storedArr', $contents);
    }

    /**
     * @covers \Core\Config\Config::store
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Nothing to save!
     */
    public function testStoreThrowsExceptionOnNoOrEmptyArgument()
    {
        Config::store();
        Config::store([]);
    }

    /**
     * @covers \Core\Config\Config::store
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Given argument must be an Array.
     */
    public function testStoreThrowsExceptionOnStringArgument()
    {
        Config::store('string');
    }

    /**
     * @covers \Core\Config\Config::store
     * @expectedException \LogicException
     * @expectedExceptionMessage Editable config file not provided.
     */
    public function testStoreThrowsExceptionWhenEditableFileNotSet()
    {
        Config::$confEditablePath = null;
        Config::store(['storedArr' => 'storedVal']);
    }

    /**
     * @covers \Core\Config\Config::store
     * @expectedException \LogicException
     * @expectedExceptionMessage Provided config file is not readable.
     */
    public function testStoreThrowsExceptionWhenEditableFileNotReadable()
    {
        Config::$confEditablePath = "/config/override.conf.php";
        Config::store(['storedArr' => 'storedVal']);
    }

    /**
     * @covers \Core\Config\Config::getFileContent
     */
    public function testGetFileContentAlwaysReturnsArray()
    {
        $content = Config::getFileContent('/config/override.conf.php');
        $this->assertInternalType('array', $content);
    }

    /**
     * @covers \Core\Config\Config::getFileContent
     */
    public function testGetFileContentWorksWithValidFile()
    {
        $content = Config::getFileContent(static::$frameworkConf);
        $this->assertInternalType('array', $content);
        $this->assertArrayHasKey('$global', $content);
    }

    /**
     * @covers \Core\Config\Config::putFileContent
     * @throws \ErrorException
     */
    public function testPutFileContentWorksWithValidFileAndArgument()
    {
        Config::putFileContent(static::$editableConf, ['putArr' => ['putKey' => 'putVal']]);

        $content = include static::$editableConf;
        $this->assertArrayHasKey('putArr', $content);
    }

    /**
     * @covers \Core\Config\Config::putFileContent
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Content to put in file must be an Array.
     */
    public function testPutFileContentThrowsExceptionOnStringArgument()
    {
        Config::putFileContent(_ROOT . static::$editableConf, 'someString');
    }

    /**
     * @covers \Core\Config\Config::putFileContent
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Nothing to put in file.
     */
    public function testPutFileContentThrowsExceptionOnEmptyArray()
    {
        Config::putFileContent(_ROOT . static::$editableConf, []);
    }

    /**
     * @covers \Core\Config\Config::putFileContent
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Given file not readable Or writable.
     */
    public function testPutFileContentThrowsExceptionOnInvalidFile()
    {
        Config::putFileContent('/someFile.php', ['putArr' => ['putKey' => 'putVal']]);
    }
}