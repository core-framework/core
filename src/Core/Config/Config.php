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

namespace Core\Config;

use Core\Contracts\ConfigContract;
use Symfony\Component\Config\Definition\Exception\Exception;

class Config implements ConfigContract
{
    protected static $instance;
    public static $confDir;
    public static $confFilesData = [];

    public static $baseConfFiles = [
        '$global' => 'global.conf.php',
        '$db' => 'db.conf.php',
        '$routes' => 'routes.conf.php',
        '$env' => 'env.conf.php',
        '$services' => 'services.conf.php'
    ];

    public static $cliConfFiles = [
        '$global' => 'global.conf.php',
        '$db' => 'db.conf.php',
        '$services' => 'cliServices.conf.php',
        '$commands' => 'commands.conf.php',
        '$options' => 'options.conf.php'
    ];

    public static $allConf = [
        '$global' => [],
        '$db' => [],
        '$routes' => [],
        '$env' => [],
        '$services' => [],
        '$commands' => [],
        '$options' => []
    ];

    /**
     * Constructor
     *
     * @param null $configDir
     */
    public function __construct($configDir = null)
    {
        if (!is_null($configDir)) {

            if (!is_string($configDir)) {
                $type = gettype($configDir);
                throw new \InvalidArgumentException(
                    "Config must be initiated with Config Directory Path (string). {$type} given."
                );
            }

            static::setConfDir($configDir);
            static::setUp();
            static::setInstance($this);
        }

        return $this;
    }

    /**
     * @param null $confDir
     * @return Config
     */
    public static function getInstance($confDir = null)
    {
        if (!isset(static::$instance)) {
            static::$instance = new static($confDir);
        }

        return static::$instance;
    }

    /**
     * @param Config|null $confDir
     * @return static
     */
    public static function getNewInstance($confDir = null)
    {
        return static::$instance = new static($confDir);
    }

    /**
     * @param Config $instance
     */
    public static function setInstance(Config $instance)
    {
        static::$instance = $instance;
    }

    /**
     * Setup/initiate Config Class
     */
    protected static function setUp()
    {
        $confDir = static::getConfDir();
        $env = getenv('environment');
        $confArr = [];

        if (is_readable($confDir . '/framework.conf.php')) {
            self::$allConf = include($confDir . '/framework.conf.php');
        } elseif (is_readable($confDir . '/cli.conf.php')) {
            self::$allConf = include($confDir . '/cli.conf.php');
        } else {

            $filesArr = php_sapi_name() === 'cli' ? static::$cliConfFiles : static::$baseConfFiles;

            foreach ($filesArr as $key => $file) {
                $orgFile = $confDir . '/' . $file;
                $overrideFile = $confDir . '/' . $env . '/' . $file;

                if (is_readable($orgFile)) {
                    $confArr = include($orgFile);
                    if (!is_array($confArr)) {
                        $confArr = [];
                    }
                }

                if (is_readable($overrideFile)) {
                    $confArr = array_merge_recursive($confArr, $overrideFile);
                }

                static::$allConf[$key] = $confArr;
            }

        }

        $config = static::$allConf;

        if (isset($config['$global']['apcIsLoaded']) && isset($config['$global']['apcIsEnabled'])) {
            $config['$global']['hasAPC'] = true;
        } else {
            $config['$global']['hasAPC'] = false;
        }

        if ($env === 'local' || ($env === 'production' && isset($config['$env']['debug']) && $config['$env']['debug'] === true)) {

            ini_set('display_errors', 'On');
            if (isset($config['$env']['error_reporting_type'])) {
                error_reporting($config['$env']['error_reporting_type']);
            } else {
                error_reporting(E_ALL);
            }

        } else {
            ini_set('display_errors', 'Off');
            error_reporting(0);
        }

    }

    /**
     * @inheritdoc
     */
    public static function getConfDir()
    {
        return self::$confDir;
    }

    /**
     * @inheritdoc
     */
    public static function setConfDir($confDir)
    {
        self::$confDir = rtrim($confDir, '/');
    }

    /**
     * @inheritdoc
     */
    public static function getDatabase()
    {
        return self::get('$db');
    }

    /**
     * @inheritdoc
     */
    public static function get($confKey = null, $default = false)
    {
        try {
            if (is_null($confKey)) {
                $return = static::$allConf;
            } elseif (strpos($confKey, ':') !== false) {
                $return = self::getFromFile($confKey);
            } else {
                if (isset(static::$allConf[$confKey])) {
                    $return = static::$allConf[$confKey];
                } elseif (strpos($confKey, '.') !== false) {
                    $return = dotGet($confKey, static::$allConf);
                } else {
                    $return = searchArrayByKey(static::$allConf, $confKey);
                }
            }
        } catch (Exception $e) {
            $return = $default;
        }
        
        if (empty($return) || $return == false) {
            $return = $default;
        }
        
        return $return;
    }

    /**
     * Get Config from file given (dot notation) path or key and filePath
     *
     * @param $confKey
     * @return array|null
     */
    protected static function getFromFile($confKey)
    {
        $fileName = static::getFileNameFromKey($confKey);
        $pathKey = static::getPathFromKey($confKey);
        $env = self::getEnvironment();

        $orgFilePath = static::getConfDir() . '/' . $fileName;
        $overrideFilePath = static::getConfDir() . '/' . $env . '/' . $fileName;

        if (isset(static::$confFilesData[$fileName])) {
            $array = static::$confFilesData[$fileName];
        }
        elseif (is_readable($overrideFilePath) && is_readable($orgFilePath)){
            $overrideArr = include($overrideFilePath);
            $orgFilePath = include($orgFilePath);
            $array = array_merge($orgFilePath, $overrideArr);
            static::$confFilesData[$fileName] = $array;
        } elseif (is_readable($orgFilePath)) {
            $array = include $orgFilePath;
            static::$confFilesData[$fileName] = $array;
        } else {
            throw new \LogicException("Given Config File not found or not readable.");
        }

        if (!is_array($array)) {
            throw new \LogicException("Expects config file content to be an array.");
        }

        return dotGet($pathKey, $array);
    }

    protected static function getFileNameFromKey($confKey)
    {
        $fileName = null;
        $parts = explode(':', $confKey);
        $file = $parts[0];
        if (is_readable(static::$confDir . '/' . $file . '.conf.php')) {
            $fileName = $file . '.conf.php';
        } elseif (is_readable(static::$confDir . '/' . $file . '.php')) {
            $fileName = $file . '.php';
        } else {
            $fileName = $file;
        }

        return $fileName;
    }

    /**
     * Extracts path (dot notation) or Key from given config Key
     *
     * @param $confKey
     * @return string
     */
    protected static function getPathFromKey($confKey)
    {
        $parts = explode(':', $confKey);
        $path = array_slice($parts, 1)[0];
        if (empty($path) || $path === null) {
            $path = '';
        }
        return $path;
    }

    /**
     * @inheritdoc
     */
    public static function getEnvironment()
    {
        return getOne(getenv('environment'), 'local');
    }

    /**
     * @inheritdoc
     */
    public static function getGlobal()
    {
        return self::get('$global');
    }

    /**
     * @inheritdoc
     */
    public static function getRoutes()
    {
        return self::get('$routes');
    }

    /**
     * @inheritdoc
     */
    public static function getServices()
    {
        return self::get('$services');
    }

    /**
     * @inheritdoc
     */
    public static function add($name, array $confArr)
    {
        static::$allConf[$name] = $confArr;
    }

    /**
     * @inheritdoc
     */
    public static function set(array $confArr)
    {
        static::$allConf = $confArr;
    }

    /**
     * Determines the file Path from provided Config Key
     *
     * @param $confKey
     * @return string
     */
    protected static function getFilePathFromKey($confKey)
    {
        $filePath = '';
        $parts = explode(':', $confKey);
        $file = $parts[0];
        if (is_readable(static::$confDir . '/' . $file . '.conf.php')) {
            $filePath = static::$confDir . '/' . $file . '.conf.php';
        } elseif (is_readable(static::$confDir . '/' . $file . '.php')) {
            $filePath = static::$confDir . '/' . $file . '.php';
        } else {
            $filePath = static::$confDir . '/' . $file;
        }

        return $filePath;
    }

}