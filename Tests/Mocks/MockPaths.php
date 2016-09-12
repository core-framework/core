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


namespace Core\Tests\Mocks;


use Core\Contracts\Application;
use org\bovigo\vfs\vfsStream;

class MockPaths
{
    /**
     * @var Application
     */
    public $app;
    public static $basePath;
    public static $frameworkConf;
    public static $viewConf;
    public static $databaseConf = [
        'default' => 'mysql',

        'connections' => [
            'mysql' => [
                'mapper' => \Core\Database\Mapper\MySqlMapper::class,
                'type' => 'mysql',
                'db' => 'test',
                'host' => '127.0.0.1',
                'user' => 'root',
                'pass' => 'qwedsa',
                'options' => []
            ]
        ]

    ];
    public static $appConf = [];
    public static $routerConf = ['controller' => ['namespace' => 'app\\Controllers']];
    public static $envConf = [];
    public static $servicesConf = [
        'Smarty' => \Smarty::class,
        'View' => [
            \Core\View\View::class,
            ['App']
        ],
        'testService' => \stdClass::class
    ];
    public static $commandsConf = [
        'test' => \Core\Tests\Stubs\Commands\TestCommand::class
    ];
    public static $optionsConf = [];

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

    public static function createMockPaths()
    {
        $files = [];
        vfsStream::setup('root', 0777, self::$structure);
        static::$basePath = vfsStream::url('root');

        $files['appConf'] = vfsStream::url('root/config/app.php');
        $files['databaseConf'] = vfsStream::url('root/config/database.php');
        $files['routerConf'] = vfsStream::url('root/config/router.php');
        $files['envConf'] = vfsStream::url('root/config/env.php');
        $files['servicesConf'] = vfsStream::url('root/config/services.php');
        $files['commandsConf'] = vfsStream::url('root/config/commands.php');
        $files['optionsConf'] = vfsStream::url('root/config/options.php');
        //static::$frameworkConf = vfsStream::url('root/config/app.php');
        //static::$viewConf = vfsStream::url('root/config/view.php');

        static::setFilePermissions();
        static::initConfFiles($files);
    }

    public static function setFilePermissions()
    {
        chmod(vfsStream::url('root/storage/framework/cache'), 0777);
        chmod(vfsStream::url('root/storage/smarty_cache/'), 0777);
        chmod(vfsStream::url('root/storage/smarty_cache/cache'), 0777);
        chmod(vfsStream::url('root/storage/smarty_cache/templates_c/'), 0777);
    }

    public static function initConfFiles($filesArr)
    {
        foreach ($filesArr as $key => $file) {
            $data = '<?php return ' . var_export(static::${$key}, true) . ";\n ?>";
            file_put_contents($file, $data);
        }
    }
}