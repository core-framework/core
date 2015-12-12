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

namespace Core\Application;

use Core\Cache\AppCache;
use Core\Container\Container;
use Core\Contracts\BaseApplicationContract;
use Core\Contracts\CacheContract;
use Core\Response\Response;
use Core\Router\Router;
use Core\Contracts\ViewContract;

/**
 * Base Application class
 *
 * Class BaseApplication
 * @package Core\Application
 */
abstract class BaseApplication extends Container implements BaseApplicationContract
{
    /**
     * Application Development State Flag
     */
    const DEVELOPMENT_STATE = 'dev';

    /**
     * Application Production State Flag
     */
    const PRODUCTION_STATE = 'prod';

    /**
     * Begin state
     *
     * @var int
     */
    const STATUS_BOOTING = 0;

    /**
     * Application has completed booting
     *
     * @var int
     */
    const STATUS_BOOTED = 1;

    /**
     * Request handling state
     *
     * @var int
     */
    const STATUS_HANDLING_REQUEST = 2;

    /**
     * Loading from cache state
     *
     * @var int
     */
    const STATUS_LOADING_FROM_CACHE = 3;

    /**
     * Computing response state
     *
     * @var int
     */
    const STATUS_COMPUTING_RESPONSE = 4;

    /**
     * Response sending state
     *
     * @var int
     */
    const STATUS_SENDING_RESPONSE = 5;

    /**
     * Post response state
     *
     * @var int
     */
    const STATUS_SENT_RESPONSE = 6;

    /**
     * End state
     *
     * @var int
     */
    const STATUS_SHUTDOWN = 7;

    /**
     * Contains the Application object in its current state
     *
     * @var $app Application
     */
    public static $app;

    /**
     * To explicitly turn on error reporting and error display
     *
     * @var $isDebugMode
     */
    public static $isDebugMode;

    /**
     * Application Name
     *
     * @var string
     */
    protected static $applicationName = "Core Framework";

    /**
     * Application Version
     *
     * @var string
     */
    protected static $version = '1.0.0';

    /**
     * Application base/root path
     *
     * @var string
     */
    private static $basePath;

    /**
     * Application folder path
     *
     * @var string
     */
    private static $appPath;

    /**
     * Application Document Root
     *
     * @var
     */
    private static $docRoot;

    /**
     * Array of base/core Components
     *
     * @var array
     */
    public $baseServices = [];

    /**
     * Contains the class maps
     *
     * @var array
     */
    public static $classmap;

    /**
     * Class map aliases
     *
     * @var array
     */
    public static $alias = [
        '@web' => '@base/web',
        '@app' => '@base/app'
    ];

    /**
     * Configurations for the application
     *
     * @var array
     */
    public $config;

    /**
     * Cache instance
     *
     * @var AppCache
     */
    public $cache;

    /**
     * Router instance
     *
     * @var \Core\Router\Router
     */
    public $router;

    /**
     * View instance
     *
     * @var \Core\Contracts\ViewContract
     */
    public $view;

    /**
     * Response instance
     *
     * @var \Core\Response\Response
     */
    public $response;

    /**
     * When static::DEVELOPMENT_STATE or 'dev' ensures errors are displayed
     *
     * @var $appEnv string
     * @supported static::DEVELOPMENT_STATE || static::PRODUCTION_STATE
     */
    protected $appEnv;

    /**
     * default charset to us Application
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * Application default language
     *
     * @var string
     */
    protected $language = 'en_US';

    /**
     * Time To Live value for Application caching
     *
     * @var int
     */
    protected $ttl = 3600;

    /**
     * Current Application status
     *
     * @var int
     */
    private $status;

    /**
     * Stores the Router cache Key for current Route
     *
     * @var string
     */
    public $routeKey;

    /**
     * Stores the Response Cache Key for the current Route
     *
     * @var string
     */
    public $responseKey;

    /**
     * Current Router path
     *
     * @var string
     */
    public $requestedURI;

    /**
     * Application Constructor
     *
     * @param null $basePath
     */
    public function __construct($basePath = null)
    {
        is_null($basePath) ?: $this->setPaths($basePath);
        $this->boot();
        $this->setStatus(self::STATUS_BOOTED);
    }

    /**
     * Set required paths
     *
     * @param $basePath
     */
    private function setPaths($basePath)
    {
        $this->setBasePath($basePath);
        $this->setAppPath();
        $this->setDocumentRoot();
    }

    /**
     * Boot Application Services
     *
     * @return void
     */
    public function boot()
    {
        $this->setStatus(self::STATUS_BOOTING);
        $this->registerApp();
        $this->loadBaseComponents();
        $this->clearCacheIfRequired();
        $this->configure();
    }

    /**
     * Configure Application
     */
    protected function configure()
    {
        $basePath = $this->basePath();
        if (!is_null($basePath)) {
            $this->loadConfig();

            if (!is_null($this->config)) {
                $this->setEnvironment();
                $this->registerServicesFromConfig();
                $this->setRouterConf();
            }
        }
    }

    /**
     * Register Application
     *
     * @throws \ErrorException
     */
    protected function registerApp()
    {
        static::$app = $this;
        $this->register('App', $this);
    }

    /**
     * Register all provided Components.
     *
     * @return void
     */
    public function loadBaseComponents()
    {
        $this->registerAndLoad('Router', \Core\Router\Router::class);
        $this->registerAndLoad(
            'Cache',
            \Core\Cache\AppCache::class,
            [$this->getAbsolutePath("/storage/framework/cache")]
        );
    }

    /**
     * Get complete path relative to base/root path
     *
     * @param $relativePath
     * @return string
     */
    protected function getAbsolutePath($relativePath)
    {
        return $this->basePath() . $relativePath;
    }

    /**
     * Get Application Base Path
     *
     * @return string
     */
    public function basePath()
    {
        return self::$basePath;
    }

    /**
     * Load configurations from cache if exist or from file
     *
     * @throws \ErrorException
     */
    protected function loadConfig()
    {
        /** @var AppCache $cache */
        $cache = $this->cache;

        if (!$this->cache instanceof CacheContract) {
            throw new \ErrorException("Cache Service not found.");
        }

        if ($cache->cacheExists('framework.conf')) {
            $this->config = $cache->getCache('framework.conf');
        } else {
            $this->config = $this->getConfig();
            $cache->cacheContent('framework.conf', $this->config, 0);
        }
    }

    /**
     * Set Application Environment
     */
    public function setEnvironment()
    {
        $config = $this->getConfig();

        if (isset($config['$env']['app_env']) && strstr($config['$env']['app_env'], 'prod')) {
            $this->appEnv = static::PRODUCTION_STATE;
        } else {
            $this->appEnv = static::DEVELOPMENT_STATE;
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        }

        if (isset($config['$env']['debug']) && $config['$env']['debug'] === true) {

            static::$isDebugMode = $config['$env']['debug'];
            if (ini_get('display_errors') === 'off' || ini_get('display_errors') === false) {
                ini_set('display_errors', 'On');
            }

            if (isset($config['$env']['error_reporting_type'])) {
                error_reporting($config['$env']['error_reporting_type']);
            } else {
                error_reporting(E_ALL);
            }

        } else {
            static::$isDebugMode = false;
        }
    }

    /**
     * Gets configuration Array
     *
     * @return array|mixed
     */
    protected function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = require($this->getConfigPath());
        }

        return $this->config;
    }

    /**
     * Get path to "framework.conf.php" file
     *
     * @return mixed
     */
    public function getConfigPath()
    {
        return $this->getAbsolutePath('/config/framework.conf.php');
    }

    /**
     * Register Components defined in config file
     */
    protected function registerServicesFromConfig()
    {
        $this->baseServices = isset($this->config['$services']) ? $this->config['$services'] : [];

        if (!isset ($this->baseServices) || empty($this->baseServices)) {
            return;
        }

        $baseServices = $this->baseServices;
        foreach ($baseServices as $class => $definition) {
            if (is_array($definition) && $class != "commands") {
                $this->register($class, $definition['definition'])->setArguments($definition['dependencies']);
            } else {
                $this->register($class, $definition);
            }
        }
    }

    /**
     * @throws \ErrorException
     */
    protected function setRouterConf()
    {
        if (is_null($this->router)) {
            $this->router = $this->get('Router');
        }

        $this->router->setConfig($this->getConfig());
    }

    /**
     * Run Application
     *
     * @return mixed
     */
    public function run()
    {
        $this->response = $this->handle($this->router);

        if (method_exists($this->response, 'send')) {
            $this->response->send();
        }

        $this->terminate();
    }

    /**
     * Handles current route
     *
     * @param Router $router
     * @return null|Response
     * @throws \ErrorException
     */
    protected function handle(Router $router)
    {
        $this->requestedURI = $router->path;
        $this->setCacheKeys($router);

        if ($this->cache->cacheExists($this->responseKey)) {
            $this->setStatus(self::STATUS_LOADING_FROM_CACHE);
            $response = $this->getResponseFromCache();

            if ($response instanceof Response) {
                return $response;
            }
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) === true && $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')
        {
            $router = $this->cache->getCache($this->routeKey);

            if ($router instanceof Router) {
                $this->router = $router;
            }
        }

        return $this->parseRoute();

    }

    /**
     * Parse current route in Router
     *
     * @return mixed
     * @throws \ErrorException
     */
    protected function parseRoute()
    {
        $this->setStatus(self::STATUS_HANDLING_REQUEST);
        $useAestheticRouting = isset($this->config['$global']['useAestheticRouting']) ? $this->config['$global']['useAestheticRouting'] : false;
        $this->router->resolve($useAestheticRouting);
        $this->cacheRouter();

        if ($this->router->customServe === true ) {
            return $this->handleCustomServe();
        }

        return $this->loadController();
    }


    protected function handleCustomServe(ViewContract $view = null)
    {
        if (empty($this->router->resolvedArr)) {
            throw new \ErrorException("Unable to resolve Path!");
        }

        if (is_null($view)) {
            $this->view = $this->get('View');
        }

        $routeParams = $this->router->resolvedArr;
        $args = $routeParams['args'];
        $routeVars = $routeParams['routeVars'];
        $showHeader = isset($routeVars['showHeader']) && $routeVars['showHeader'] === true ? true : false;
        $showFooter = isset($routeVars['showFooter']) && $routeVars['showFooter'] === true ? true : false;
        $serveIframe = isset($routeVars['serveIframe']) && $routeVars['serveIframe'] === true ? true : false;
        $fileName = $this->router->fileName;
        $fileName = !empty($fileName) ? $fileName : 'index';
        $fileExt = $this->router->fileExt;
        $fileExt = !empty($fileExt) ? $fileExt : 'html';

        $referencePath = $this->router->referencePath;
        $rPathArr = explode('/', $referencePath);
        $realPath = static::$appPath . '/';

        foreach ($rPathArr as $part) {
            $realPath .= $part . DS;
        }

        if (!empty($args)) {
            $key = key($args);
            $realPath .= $args[$key];
        } else {
            $realPath .= $fileName . "." . $fileExt;
        }

        if ($showHeader === true && $serveIframe === false && $fileExt === 'html') {
            $this->view->showHeader = $showHeader;
            $this->view->showFooter = $showFooter;
            $this->view->setTemplateVars('customServePath', $realPath);
            $this->view->setDebugMode(false);

        } elseif ($serveIframe === true && $showHeader === true) {
            $this->view->showHeader = $showHeader;
            $this->view->showFooter = $showFooter;
            $this->view->setTemplateVars('iframeUrl', $referencePath);
            $this->view->setDebugMode(false);
        }

        return $this->loadController($this->view);
    }

    protected function loadController(ViewContract $view = null)
    {
        if (is_null($view)) {
            $view = $this->get('View');
        }

        $this->setStatus(self::STATUS_COMPUTING_RESPONSE);
        $namespace = $this->router->getNamespace();
        $controller = $this->router->getController();
        $method = $this->router->getMethod();
        $args = $this->router->getArgs();
        $class = $namespace . "\\" . $controller;
        $controllerObj = new $class($this->router, $view, $this->config);
        $response = $controllerObj->$method(!isset($args) ?: $args);

        return $response;
    }

    /**
     * Caches the current Router instance if not an ajax request OR if 'noCacheRoute' is not set in routes.conf
     * for current route
     *
     * @throws \ErrorException
     */
    protected function cacheRouter()
    {
        if ((isset($routeParams['noCacheRoute']) === true && $routeParams['noCacheRoute'] === true) && $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            $this->cache->cacheContent($this->routeKey, $this->router, $this->getTtl());
        }
    }

    /**
     * @return bool | \Core\Response\Response
     * @throws \ErrorException
     */
    protected function getResponseFromCache()
    {
        return $this->cache->getCache($this->responseKey);
    }

    /**
     * Generates and sets Cache Keys for current Route
     *
     * @param Router $router
     */
    protected function setCacheKeys(Router $router)
    {
        $path = $router->path;
        $this->routeKey = md5($path . '_route_' . session_id());
        $this->responseKey = md5($path . '_response_' . session_id());
    }

    /**
     * Clear cache if explicitly set in route
     *
     * @throws \ErrorException
     * @throws \Exception
     */
    private function clearCacheIfRequired()
    {
        if (isset($_GET['action']) && $_GET['action'] === 'clear_cache') {
            $this->cache->clearCache();
        }
    }

    /**
     * Get Application Version
     *
     * @return string
     */
    public function version()
    {
        return static::$version;
    }

    /**
     * Get current Environment state
     *
     * @return string
     */
    public function environment()
    {
        return $this->appEnv;
    }

    /**
     * Check if Application is Down
     *
     * @return bool
     */
    public function isDown()
    {
        return file_exists($this->getAbsolutePath('/storage/framework/down.php'));
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Application auto loader
     *
     * @param $class
     */
    public static function autoload($class)
    {
        if (static::$classmap[$class]) {
            $classFile = static::$classmap[$class];
            if (strpos($classFile, '@') === -1) {
                include $classFile;
                return;
            }
        } elseif (strpos($class, '\\') !== false) {
            $classFile = '@' . str_replace('\\', '/', $class);
        } else {
            return;
        }

        $realPath = self::getRealPath($classFile);

        if (!is_readable($realPath)) {
            return;
        }

        include $realPath;
    }

    /**
     * Get real path from provided aliased path
     *
     * @param $aliasPath
     * @return string
     */
    public static function getRealPath($aliasPath)
    {
        $alias = substr($aliasPath, 0, strpos($aliasPath, '/'));
        $relativePath = substr($aliasPath, strpos($aliasPath, '/'));

        $realPath = self::getAlias($alias) . $relativePath . '.php';
        return $realPath;
    }

    /**
     * Alias to path conversion
     *
     * @param $aliasKey
     * @return string
     */
    public static function getAlias($aliasKey)
    {
        if ($aliasKey === '@base') {
            return self::getBasePath();
        }

        if (isset(self::$alias[$aliasKey])) {
            $aliasVal = self::$alias[$aliasKey];
        } else {
            return;
        }

        if (strpos($aliasVal, '@') > -1) {
            $newAlias = substr($aliasVal, 0, strpos($aliasVal, '/'));
            $newAliasVal = substr($aliasVal, strpos($aliasVal, '/'));
            $aliasVal = self::getAlias($newAlias) . $newAliasVal;
        }

        return $aliasVal;
    }

    /**
     * Add alias
     *
     * @param $aliasName
     * @param $path
     */
    public static function addAlias($aliasName, $path)
    {
        if (strncmp($aliasName, '@', 1)) {
            $aliasName = '@'.$aliasName;
        }
        self::$alias[$aliasName] = $path;
    }

    /**
     * @return string
     */
    public static function getBasePath()
    {
        return self::$basePath;
    }

    /**
     * @param string $basePath
     */
    public static function setBasePath($basePath)
    {
        $basePath = rtrim($basePath, "/");
        self::$basePath = $basePath;
        self::addAlias("@base", $basePath);
    }

    /**
     * @return string
     */
    public function getAppPath()
    {
        return self::$appPath;
    }

    /**
     * @param string $appPath
     */
    public function setAppPath($appPath = null)
    {
        $appPath = is_null($appPath) ? $this->getBasePath() . "/app" : $appPath;
        $appPath = rtrim($appPath, "/");
        self::$appPath = $appPath;
        self::addAlias("@app", $appPath);
    }

    /**
     * Method to get the Current Document root
     *
     * @return mixed
     */
    public function getDocRoot()
    {
        return self::$docRoot;
    }

    /**
     * Set current Document Root
     *
     * @param null $docRoot
     */
    public function setDocumentRoot($docRoot = null)
    {
        $docRoot = is_null($docRoot) ? $this->getBasePath() : $docRoot;
        $docRoot = rtrim($docRoot, "/");
        self::$docRoot = $docRoot;
        self::addAlias("@docRoot", $docRoot);
    }

    /**
     * Method Returns current cache ttl
     *
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Register and load base components
     *
     * @param $name
     * @param $definition
     * @param array|null $arguments
     * @param bool|true $shared
     * @throws \ErrorException
     */
    public function registerAndLoad($name, $definition, array $arguments = null, $shared = true)
    {
        $service = $this->register($name, $definition, $shared);

        if (!is_null($arguments)) {
            $service->setArguments($arguments);
        }

        self::$app->{strtolower($name)} = $this->get($name);
    }

    /**
     * is utilized for reading data from inaccessible members.
     *
     * @param $name string
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     */
    function __get($name)
    {
        if (isset($this->$name)) {
            return $this->get($name);
        } elseif (!isset($this->$name) && $this->serviceExists($name)) {
            return $this->$name;
        } else {
            return false;
        }
    }

    /**
     * run when writing data to inaccessible members.
     *
     * @param $name string
     * @param $value mixed
     * @return void
     * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     */
    function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * Application End
     */
    public function terminate()
    {
        $this->setStatus(self::STATUS_SHUTDOWN);
    }


    public function __destruct()
    {

    }
}
