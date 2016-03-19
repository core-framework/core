<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 11/01/15
 * Time: 10:44 AM
 */

namespace Core\Tests\Console;

use Core\Console\Console;
use Core\Console\IOStream;
use Core\Container\Container;
use org\bovigo\vfs\vfsStream;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    public static $basePath;
    public static $frameworkConf;
    public static $frameworkConfArr = [
        '$db' => [],
        '$global' => [],
        '$routes' => []
    ];
    public static $structure = [
        'storage' => [
            'framework' => [
                'cache' => [
                    'emptyFile.php' => ""
                ]
            ]
        ],
        'config' => [
            'cli.conf.php' => ""
        ]
    ];

    public $io;

    public function setUp()
    {
        $this->_createMockPaths();
        $this->io = new IOStream();
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    public function _createMockPaths()
    {
        vfsStream::setup('root', 0777, self::$structure);
        static::$frameworkConf = vfsStream::url('root/config/cli.conf.php');
        static::$basePath = vfsStream::url('root');
        static::_initConfFiles();
    }

    public static function _initConfFiles()
    {
        $data2 = '<?php return ' . var_export(static::$frameworkConfArr, true) . ";\n ?>";
        file_put_contents(static::$frameworkConf, $data2);
    }

    /**
     * @covers \Core\Console\CLI::__construct
     */
    public function testCLIConstructor()
    {
        $cli = new Console(static::$basePath, $this->io);
        $this->assertInstanceOf('\\Core\\Console\\Console', $cli);

        $this->assertInstanceOf('\\Core\\Console\\IOStream', $cli->io);
    }

    /**
     * @covers \Core\Console\CLI::loadConf
     */
    public function testIfServiceIsSetWhenProvided()
    {
        $conf = [
            '$services' => [
                'testService' => \stdClass::class
            ]
        ];

        self::$frameworkConfArr = array_merge(self::$frameworkConfArr, $conf);
        $this->_initConfFiles();

        $testCLI = new Console(static::$basePath, $this->io);
        $this->assertInstanceOf('\\stdClass', $testCLI->get('testService'));
    }

    /**
     * @covers \Core\Console\CLI::loadConf
     */
    public function testIfCommandExistsWhenProvided()
    {
        $conf = [
            '$commands' => [
                0 => [
                    'name' => 'hello:world',
                    'shortName' => '',
                    'description' => 'Simple Hello World Command',
                    'definition' => '\\Core\\Console\\CliApplication::helloWorld',
                    'arguments' => [
                        'name' => 'name',
                        'isRequired' => false,
                        'description' => 'Your Name'
                    ]
                ]
            ]
        ];
        self::$frameworkConfArr = array_merge(self::$frameworkConfArr, $conf);
        $this->_initConfFiles();

        $testCLI = new Console(self::$basePath, $this->io);
        $this->assertArrayHasKey('hello:world', $testCLI->commands);
        $this->assertInstanceOf('\\Core\\Console\\Command', $testCLI->commands['hello:world']);
        $this->assertInternalType('callable', $testCLI->commands['hello:world']->getDefinition());
    }

    /**
     * @covers \Core\Console\CLI::loadConf
     */
    public function testIfOptionExistsWhenProvided()
    {
        $conf = [
            '$options' => [
                0 => [
                    'name' => 'hello:world',
                    'shortName' => 'H',
                    'description' => 'Simple Hello World Command',
                    'definition' => '\\Core\\Console\\CliApplication::helloWorld'
                ]
            ]
        ];
        self::$frameworkConfArr = array_merge(self::$frameworkConfArr, $conf);
        $this->_initConfFiles();

        $testCLI = new Console(self::$basePath, $this->io);
        $options = $testCLI->getOptions();
        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('hello:world', $options);
        $this->assertInstanceOf('\\Core\\Console\\Options', $options['hello:world']);
        $this->assertInternalType('callable', $options['hello:world']->getDefinition());
    }

    /**
     * @covers \Core\Console\CLI::setDefaults
     */
    public function testIfDefaultOptionsAreSet()
    {
        $testCLI = new Console(self::$basePath, $this->io);
        $options = $testCLI->getOptions();
        $this->assertArrayHasKey('help', $options);
    }

}
