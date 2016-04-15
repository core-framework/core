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
use Core\Config\Config;
use Core\Container\Container;
use Core\Contracts\BaseApplicationContract;
use Core\Contracts\CacheContract;
use Core\Contracts\ConfigContract;
use Core\Contracts\ResponseContract;
use Core\Contracts\ViewContract;
use Core\Request\Request;
use Core\Response\Response;
use Core\Router\Route;
use Core\Router\Router;

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
    const DEVELOPMENT_STATE = 'local';

    /**
     * Application Production State Flag
     */
    const PRODUCTION_STATE = 'production';

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
    protected $baseServices = [];

    /**
     * Contains the class maps
     *
     * @var array
     */
    protected static $classmap;

    /**
     * Class map aliases
     *
     * @var array
     */
    protected static $alias = [
        '@web' => '@base/web',
        '@app' => '@base/app'
    ];

    /**
     * Configurations for the application
     *
     * @var array
     */
    public $configArr;

    /**
     * Cache instance
     *
     * @var AppCache
     */
    protected $cache;

    /**
     * Request Instance
     *
     * @var \Core\Request\Request
     */
    protected $request;

    /**
     * Router instance
     *
     * @var \Core\Router\Router
     */
    protected $router;

    /**
     * Config Instance
     *
     * @var \Core\Config\Config
     */
    protected $config;

    /**
     * Response instance
     *
     * @var \Core\Response\Response
     */
    protected $response;

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
        $this->setEnvironment();
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

            if (!is_null($this->configArr)) {
                $this->registerServicesFromConfig();
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
        $this->config = $this->registerAndMake('Config', \Core\Config\Config::class, [$this->getConfigDir()]);
        $this->router = $this->registerAndMake(
            'Router',
            \Core\Router\Router::class,
            [$this->getBasePath(), $this->getConfigArr()]
        );
        $this->cache = $this->registerAndMake(
            'Cache',
            \Core\Cache\AppCache::class,
            [$this->getAbsolutePath("/storage/framework/cache")]
        );

        $this->request = Request::createFromGlobals();
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
            $this->configArr = $cache->getCache('framework.conf');
        } else {
            $this->configArr = $this->getConfigArr();
            $cache->cacheContent('framework.conf', $this->configArr, 0);
        }
    }

    /**
     * Set Application Environment
     */
    public function setEnvironment()
    {
        $env = !isset($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? static::DEVELOPMENT_STATE : static::PRODUCTION_STATE;
        $this->appEnv = $env;
        putenv('environment=' . $env);

        return $this;
    }

    /**
     * Gets configuration Array
     *
     * @return array|mixed
     */
    protected function getConfigArr()
    {
        if (is_null($this->configArr)) {
            if ($this->config instanceof ConfigContract) {
                $this->configArr = $this->config->get();
            } else {
                $this->configArr = Config::get();
            }
        }

        return $this->configArr;
    }

    /**
     * @return Config
     */
    public function getConfigInstance()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getConfigDir()
    {
        return $this->getAbsolutePath('/config');
    }

    /**
     * Register Components defined in config file
     */
    protected function registerServicesFromConfig()
    {
        $this->baseServices = isset($this->configArr['$services']) ? $this->configArr['$services'] : [];

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
     * Run Application
     *
     * @return mixed
     */
    public function run()
    {
        $this->response = $response = $this->handle($this->request);

        // If controller does not return a response and if no headers were already sent, .i.e.
        // response was not handled by controller either, in which case we show "404 Page
        // not found", through the ErrorController
        if (!$response instanceof ResponseContract && !headers_sent()) {
            $error = '\\Core\\Controllers\\ErrorController::indexAction';
            $this->response = $response = $error();
        }

        $response->send();

        $this->terminate();
    }

    /**
     * Handles current route
     *
     * @param Request $request
     * @return null|Response
     * @throws \ErrorException
     */
    protected function handle(Request $request)
    {
        $this->requestedURI = $request->getPath();
        $this->setCacheKeys($request);

        if ($this->cache->cacheExists($this->responseKey)) {
            $this->setStatus(self::STATUS_LOADING_FROM_CACHE);
            $response = $this->getResponseFromCache();

            if ($response instanceof Response) {
                return $response;
            }
        }

        if (!$request->isAjax()) {
            $route = $this->cache->getCache($this->routeKey);

            if ($route instanceof Route) {
                return $this->router->run($route);
            }
        }

        return $this->parseRequest($request);

    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function parseRequest(Request $request)
    {
        $this->setStatus(self::STATUS_HANDLING_REQUEST);
        $response = $this->router->handle($request);

        $route = $this->router->getCurrentRoute();
        if (!is_null($route)) {
            $this->cacheRoute($route);
        }

        return $this->prepareResponse($response);
    }

    /**
     * @param $response
     * @return Response
     */
    protected function prepareResponse($response)
    {
        if (!$response instanceof ViewContract && !$response instanceof ResponseContract) {
            $response = $this->router->makeExceptionResponse(
                new \RuntimeException("Cannot Prepare Response. Argument of type " . gettype($response) . " given.")
            );
        } elseif ($response instanceof ViewContract) {
            $html = $response->fetch();
            $response = new Response($html);
        }
        
        $this->cacheResponse($response);

        return $response;
    }

    protected function cacheResponse(Response $response)
    {
        $route = $this->router->getCurrentRoute();
        if (!is_null($route) && !$this->request->isAjax() && !$route->isCacheable()) {
            $this->cache->cacheContent($this->responseKey, $response, $this->getTtl());
        }
    }

    /**
     * @deprecated
     * @param ViewContract|null $view
     * @return mixed
     * @throws \ErrorException
     */
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

    /**
     * @deprecated
     * @param ViewContract|null $view
     * @return mixed
     * @throws \ErrorException
     */
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
        $controllerObj = new $class(self::basePath(), $this->router, $view, $this->configArr);
        $response = $controllerObj->$method(!isset($args) ?: $args);

        if (!$response instanceof ResponseContract || $response->getIsContentSet()) {
            $response = $controllerObj->getResponse();
        }

        return $response;
    }

    /**
     * @param Route $route
     */
    protected function cacheRoute(Route $route)
    {
        if (!$this->request->isAjax() && !$route->isCacheable()) {
            $this->cache->cacheContent($this->routeKey, $route, $this->getTtl());
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
     * @param Request $request
     */
    protected function setCacheKeys(Request $request)
    {
        $path = $request->getPath();
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
     * Return true if current Environment is in Production state else false
     *
     * @return bool
     */
    public function isProduction()
    {
        return $this->environment() === Application::PRODUCTION_STATE;
    }

    /**
     * Return true if current Environment is in Development state else false
     *
     * @return bool
     */
    public function isDevelopment()
    {
        return $this->environment() === Application::DEVELOPMENT_STATE;
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
            $aliasName = '@' . $aliasName;
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
    public static function getAppPath()
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
        $docRoot = is_null($docRoot) ? $this->getBasePath() . "/web" : $docRoot;
        $docRoot = rtrim($docRoot, "/");
        self::$docRoot = $docRoot;
        self::addAlias("@docRoot", $docRoot);
    }

    /**
     * @return mixed|object
     * @throws \ErrorException
     */
    public function getTemplateEngine()
    {
        $tplEngine = getOne(strtolower($this->config->get('templateEngine')), 'smarty');
        if (isset($this->{$tplEngine})) {
            return $this->{$tplEngine};
        } else {
            return $this->get(ucfirst($tplEngine));
        }
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

        static::$app->{strtolower($name)} = $this->get($name);
    }

    /**
     * Register and return Class Instances
     *
     * @param $name
     * @param $definition
     * @param array|null $arguments
     * @param bool $shared
     * @return object
     * @throws \ErrorException
     */
    public function registerAndMake($name, $definition, array $arguments = null, $shared = true)
    {
        $service = $this->register($name, $definition, $shared);

        if (!is_null($arguments)) {
            $service->setArguments($arguments);
        }

        return $this->get($name);
    }

    /**
     * @return AppCache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param AppCache $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param Router $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
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
