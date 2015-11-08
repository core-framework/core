<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 13/10/15
 * Time: 11:27 AM
 */

namespace Tests\Config;


use Core\Config\AppConfig;
use org\bovigo\vfs\vfsStream;

class AppConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $config AppConfig
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
        $this->config = new AppConfig();
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
     * @covers \Core\Config\AppConfig::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnCreation()
    {
        new AppConfig('');
    }

    /**
     * @covers \Core\Config\AppConfig::__construct
     */
    public function testSetUpUsingArray()
    {
        $config = new AppConfig(array('Test' => array('testArr')));
        $this->assertInstanceOf('\\Core\\Config\\AppConfig', $config);
        $this->assertArrayHasKey('Test', AppConfig::$allConf);
    }

    /**
     * @covers \Core\Config\AppConfig::addConf
     */
    public function testAddConf()
    {
        AppConfig::addConf('addArr', array('testKey' => 'testVal'));
        $this->assertArrayHasKey('addArr', AppConfig::$allConf);
    }

    /**
     * @covers \Core\Config\AppConfig::setConf
     */
    public function testSetConf()
    {
        AppConfig::setConf(array('newArr' => array('testKey' => 'testVal')));
        $this->assertArrayHasKey('newArr', AppConfig::$allConf);
        $this->assertArrayNotHasKey('addArr', AppConfig::$allConf);
    }

    /**
     * @covers \Core\Config\AppConfig::setFile
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Given argument must be a readable file path.
     */
    public function testExceptionOnSetFile()
    {
        AppConfig::setFile("");
    }

    /**
     * @covers \Core\Config\AppConfig::setFile
     */
    public function testSetFile()
    {
        AppConfig::setFile(static::$frameworkConf);
        $this->assertArrayHasKey('$global', AppConfig::$allConf);
        $this->assertArrayHasKey('$db', AppConfig::$allConf);
        $this->assertArrayHasKey('$routes', AppConfig::$allConf);
    }

    /**
     * @covers \Core\Config\AppConfig::setEditableConfFile
     */
    public function testSetEditableConfFile()
    {
        $editablePath = static::$editableConf;
        $this->config->setEditableConfFile($editablePath);
        $this->assertEquals($editablePath, AppConfig::$confEditablePath);
    }

    /**
     * @covers \Core\Config\AppConfig::store
     * @covers \Core\Config\AppConfig::getFileContent
     */
    public function testStore()
    {
        AppConfig::store(['storedArr' => 'storedVal']);
        $this->assertFileExists(AppConfig::$confEditablePath);

        $contents = AppConfig::getFileContent(AppConfig::$confEditablePath);
        $this->assertArrayHasKey('storedArr', $contents);
    }

    /**
     * @covers \Core\Config\AppConfig::store
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Nothing to save!
     */
    public function testStoreThrowsExceptionOnNoOrEmptyArgument()
    {
        AppConfig::store();
        AppConfig::store([]);
    }

    /**
     * @covers \Core\Config\AppConfig::store
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Given argument must be an Array.
     */
    public function testStoreThrowsExceptionOnStringArgument()
    {
        AppConfig::store('string');
    }

    /**
     * @covers \Core\Config\AppConfig::store
     * @expectedException \LogicException
     * @expectedExceptionMessage Editable config file not provided.
     */
    public function testStoreThrowsExceptionWhenEditableFileNotSet()
    {
        AppConfig::$confEditablePath = null;
        AppConfig::store(['storedArr' => 'storedVal']);
    }

    /**
     * @covers \Core\Config\AppConfig::store
     * @expectedException \LogicException
     * @expectedExceptionMessage Provided config file is not readable.
     */
    public function testStoreThrowsExceptionWhenEditableFileNotReadable()
    {
        AppConfig::$confEditablePath = "/config/override.conf.php";
        AppConfig::store(['storedArr' => 'storedVal']);
    }

    /**
     * @covers \Core\Config\AppConfig::getFileContent
     */
    public function testGetFileContentAlwaysReturnsArray()
    {
        $content = AppConfig::getFileContent('/config/override.conf.php');
        $this->assertInternalType('array', $content);
    }

    /**
     * @covers \Core\Config\AppConfig::getFileContent
     */
    public function testGetFileContentWorksWithValidFile()
    {
        $content = AppConfig::getFileContent(static::$frameworkConf);
        $this->assertInternalType('array', $content);
        $this->assertArrayHasKey('$global', $content);
    }

    /**
     * @covers \Core\Config\AppConfig::putFileContent
     * @throws \ErrorException
     */
    public function testPutFileContentWorksWithValidFileAndArgument()
    {
        AppConfig::putFileContent(static::$editableConf, ['putArr' => ['putKey' => 'putVal']]);

        $content = include static::$editableConf;
        $this->assertArrayHasKey('putArr', $content);
    }

    /**
     * @covers \Core\Config\AppConfig::putFileContent
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Content to put in file must be an Array.
     */
    public function testPutFileContentThrowsExceptionOnStringArgument()
    {
        AppConfig::putFileContent(_ROOT . static::$editableConf, 'someString');
    }

    /**
     * @covers \Core\Config\AppConfig::putFileContent
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Nothing to put in file.
     */
    public function testPutFileContentThrowsExceptionOnEmptyArray()
    {
        AppConfig::putFileContent(_ROOT . static::$editableConf, []);
    }

    /**
     * @covers \Core\Config\AppConfig::putFileContent
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Given file not readable Or writable.
     */
    public function testPutFileContentThrowsExceptionOnInvalidFile()
    {
        AppConfig::putFileContent('/someFile.php', ['putArr' => ['putKey' => 'putVal']]);
    }
}