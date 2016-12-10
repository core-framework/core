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

use Core\Application\Bootstrappers\BootConfiguration;
use Core\Container\Container;
use Core\Contracts\Application as ApplicationInterface;
use Core\Contracts\Bootstrapper;
use Core\Contracts\Cache;
use Core\Contracts\Config;
use Core\Contracts\Database\Mapper;
use Core\Contracts\Events\Dispatcher;
use Core\Contracts\Events\Subscriber;
use Core\Contracts\FileSystem\FileSystem;
use Core\Contracts\Request\Request as RequestInterface;
use Core\Contracts\Response\Response as ResponseInterface;
use Core\Contracts\Router\Route;
use Core\Contracts\Router\Router;
use Core\Contracts\View;
use Core\FileSystem\Explorer;
use Core\Request\Request;
use Core\Response\Response;

class BaseApplication extends Container implements ApplicationInterface, Subscriber
{
    /**
     * Application state flags
     */
    const DEVELOPMENT_STATE = 'local';
    const PRODUCTION_STATE = 'production';
    const TESTING_STATE = 'testing';

    /**
     * Contains the Application Instance
     *
     * @var $app Application
     */
    public static $app;
    /**
     * Contains the class maps
     *
     * @var array
     */
    protected static $classmap;
    /**
     * Application Name
     *
     * @var string
     */
    protected $applicationName = "Core Framework";
    /**
     * Application Version
     *
     * @var string
     */
    protected $version = '1.0.0';
    /**
     * Class map aliases
     *
     * @var array
     */
    protected $alias = [];
    /**
     * When static::DEVELOPMENT_STATE or 'dev' ensures errors are displayed
     *
     * @var $environment string
     * @supported static::DEVELOPMENT_STATE || static::PRODUCTION_STATE
     */
    protected $environment;
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
     * @var Dispatcher $dispatcher
     */
    protected $event;
    /**
     * @var FileSystem $fileSystem
     */
    protected $fileSystem;
    /**
     * @var Cache $cache
     */
    protected $cache;
    /**
     * @var Config $config
     */
    protected $config;
    /**
     * @var Router $router
     */
    protected $router;
    /**
     * @var ResponseInterface $response
     */
    protected $response;
    /**
     * @var RequestInterface $request
     */
    protected $request;
    /**
     * @var View $view
     */
    protected $view;
    /**
     * @var array $cacheKeys
     */
    protected $cacheKeys = [];
    /**
     * @var array $bootstrappers
     */
    protected $bootstrappers = [
        '\Core\Application\Bootstrappers\BootSubscribers',
        '\Core\Application\Bootstrappers\BootConfiguration',
        //'\Core\Application\Bootstrappers\BootComponents'
    ];
    /**
     * @var array $coreComponents
     */
    protected $coreComponents = [
        'Event' => ['\Core\Events\Dispatcher', ['App']],
        'FileSystem' => '\Core\FileSystem\FileSystem',
        'Cache' => ['\Core\Cache\FileCache', ['FileSystem', '@base/storage/framework/cache/']],
    ];
    /**
     * @var array $components
     */
    protected $components = [
        'Router' => ['\Core\Router\Router', ['App']],
        'View' => ['\Core\View\View', ['App']]
    ];
    protected $mappers = [
        'mysql' => \Core\Database\Mapper\MySqlMapper::class
    ];
    /**
     * Application base/root path
     *
     * @var string
     */
    private $basePath;
    /**
     * Application folder path
     *
     * @var string
     */
    private $appPath;
    /**
     * Config directory path
     *
     * @var string
     */
    private $configPath;
    /**
     * Cache directory path
     *
     * @var string
     */
    private $cachePath;
    /**
     * Storage directory path
     *
     * @var string
     */
    private $storagePath;
    /**
     * Application Document Root
     *
     * @var
     */
    private $docRoot;

    /**
     * Application Constructor
     * Registers auto-loader and initiates boot tasks
     *
     * @param null $basePath
     */
    public function __construct($basePath = null)
    {
        spl_autoload_register([$this, 'autoload'], true, true);
        is_null($basePath) ?: $this->setPaths($basePath);
        $this->boot();
        $this->dispatch('core.app.boot.done', $this);
    }

    /**
     * Set required paths
     *
     * @param $basePath
     */
    private function setPaths($basePath)
    {
        $this->setBasePath($basePath);
        //$this->setAppPath();
        //$this->setDocumentRoot();
    }

    /**
     * Sets base path and registers it as Alias (@base) in Application
     *
     * @param $path
     * @return $this
     */
    private function setBasePath($path)
    {
        $basePath = rtrim($path, "/");
        $this->basePath = $path;
        $this->addAlias("@base", $basePath);
        return $this;
    }

    /**
     * Add alias
     *
     * @param $aliasName
     * @param $path
     */
    public function addAlias($aliasName, $path)
    {
        if (strncmp($aliasName, '@', 1)) {
            $aliasName = '@' . $aliasName;
        }
        $this->alias[$aliasName] = $path;
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->checkIfAppIsDown();
        $this->registerApp();
        $this->setEnvironment();
        /**/
        $this->registerCoreComponents();
        $this->bootstrap($this->bootstrappers);
    }

    public function checkIfAppIsDown()
    {
        if ($this->isDown() && !$this->isCLI()) {

        }
    }

    /**
     * @inheritDoc
     */
    public function isDown()
    {
        return file_exists($this->getAbsolutePath('/storage/framework/down.php'));
    }

    /**
     * Get complete path relative to base/root path
     *
     * @param $relativePath
     * @return string
     */
    public function getAbsolutePath($relativePath)
    {
        return $this->basePath . $relativePath;
    }

    /**
     * @inheritdoc
     */
    public function isCLI()
    {
        return (php_sapi_name() === 'cli');
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
        $this->register('Application', $this);
    }

    /**
     * Set Application Environment
     *
     * @param string $environment
     * @return $this
     */
    public function setEnvironment($environment = null)
    {
        // TODO: environment variable caching
        if (getenv('environment') === static::TESTING_STATE || is_null($environment)) {
            $this->detectEnvironment();
        } else {
            $this->environment = $environment;
            putenv('environment=' . $environment);
            $this->dispatch('core.app.setEnvironment', $this);
        }

        return $this;
    }

    /**
     * Detect current Environment
     */
    public function detectEnvironment()
    {
        $env = getenv('environment');
        if (isset($env)) {
            $this->environment = $env;
        } else {
            $env = $_SERVER["HTTP_HOST"] === 'localhost' && $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? static::DEVELOPMENT_STATE : static::PRODUCTION_STATE;
            $this->environment = $env;
            putenv('environment=' . $env);
        }
    }

    /**
     * Dispatch Application Event
     *
     * @param $event
     * @param array $payload
     * @return array|mixed
     */
    public function dispatch($event, $payload = [])
    {
        return $this->getDispatcher()->dispatch($event, $payload);
    }

    /**
     * @inheritdoc
     */
    public function getDispatcher()
    {
        if ($this->event) {
            return $this->event;
        }

        return $this->event = $this->get('Event');
    }

    /**
     * @return array
     */
    protected function registerCoreComponents()
    {
        return $this->registerComponents($this->coreComponents);
    }

    /**
     * @param $components
     * @return array
     */
    private function registerComponents($components)
    {
        $responses = [];
        foreach ($components as $name => $component) {
            if ($this->event) {
                $responses['core.app.' . strtolower($name) . '.preload'] = $this->dispatch(
                    'core.app.' . strtolower($name) . '.preload',
                    static::$app
                );
            }
            if (is_array($component)) {
                $this->register($name, $component[0])->setArguments($this->parseArguments($component[1]));
            } else {
                $this->register($name, $component);
            }
            $responses['core.app.' . strtolower($name) . '.postload'] = $this->dispatch(
                'core.app.' . strtolower($name) . '.postload',
                static::$app
            );
        }
        return $responses;
    }

    /**
     * @param $arguments
     * @return mixed
     */
    private function parseArguments($arguments)
    {
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        foreach ($arguments as &$argument) {
            if (is_string($argument) && strContains('@', $argument)) {
                $argument = $this->getRealPath($argument);
            }
        }

        return $arguments;
    }

    /**
     * Get real path from provided aliased path
     *
     * @param $aliasPath
     * @return string
     */
    public function getRealPath($aliasPath)
    {
        if (!strContains('@', $aliasPath)) {
            return $aliasPath;
        }
        $alias = substr($aliasPath, 0, strpos($aliasPath, '/'));
        $relativePath = substr($aliasPath, strpos($aliasPath, '/'));

        $realPath = $this->getAlias($alias) . $relativePath;
        if (!is_dir($realPath) && substr($realPath, -1) != '/') {
            $realPath .= '.php';
        }
        return $realPath;
    }

    /**
     * Alias to path conversion
     *
     * @param $aliasKey
     * @return string
     */
    public function getAlias($aliasKey)
    {
        if ($aliasKey === '@base') {
            return $this->basePath();
        }

        if (isset($this->alias[$aliasKey])) {
            $aliasVal = $this->alias[$aliasKey];
        } else {
            return;
        }

        if (strpos($aliasVal, '@') > -1) {
            $newAlias = substr($aliasVal, 0, strpos($aliasVal, '/'));
            $newAliasVal = substr($aliasVal, strpos($aliasVal, '/'));
            $aliasVal = $this->getAlias($newAlias) . $newAliasVal;
        }

        return $aliasVal;
    }

    /**
     * @inheritDoc
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Bootstrap application
     *
     * @param $bootstrappers
     */
    protected function bootstrap($bootstrappers)
    {
        foreach ($bootstrappers as $bootstrapper) {
            /** @var Bootstrapper $bootable */
            $bootable = new $bootstrapper();
            $bootable->bootstrap($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function bind($name, $definition, $singleton = true)
    {
        $service = $this->register($name, $definition, $singleton);
        $this->dispatch('core.app.' . strtolower($name) . '.registered', $service);
    }

    /**
     * @inheritdoc
     */
    public function build($definition, $arguments = null, $name = null, $singleton = true)
    {
        $this->{strtolower($name)} = $this->make($definition, $arguments, $name, $singleton);
        return $this->dispatch('core.app.' . strtolower($name) . '.booted', $this->{strtolower($name)});
    }

    /**
     * Subscribe to Application events
     *
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->on('core.app.config.booted', [$this, 'loadBootstrappersFromConfig'], 0);
        $dispatcher->on('core.app.config.booted', [$this, 'loadSubscribers'], 1);
        $dispatcher->on('core.app.config.booted', [$this, 'loadServices'], 2);
        $dispatcher->on('core.app.config.booted', [$this, 'cacheConfig'], 3);
        $dispatcher->on('core.app.config.booted', [$this, 'setAppPathFromConf'], 4);
        $dispatcher->on('core.app.config.booted', [$this, 'setDocumentRootFromConf'], 5);
        $dispatcher->on('core.app.router.postload', [$this, 'bootstrapRouter'], 0);
        $dispatcher->on('core.app.handle.pre', [$this, 'preHandle'], 0);
        $dispatcher->on('core.router.matched', [$this, 'cacheRoute'], 0);
        $dispatcher->on('core.app.setEnvironment', [new BootConfiguration(), 'bootstrap'], 0);
    }

    /**
     * @param Config $config
     */
    public function loadBootstrappersFromConfig(Config $config)
    {
        $this->bootstrap($config->get('bootstrappers', []));
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setAppPathFromConf(Config $config)
    {
        $path = $config->get('app.appPath', '/app');
        $this->appPath = $this->getAbsolutePath($path);
        $this->addAlias('@app', $this->appPath);
        return $this;
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setDocumentRootFromConf(Config $config)
    {
        $path = $config->get('app.publicPath', '/web');
        $this->docRoot = $this->getAbsolutePath($path);
        $this->addAlias("@docRoot", $this->docRoot);
        $this->addAlias("@public", $this->docRoot);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function loadConfigFromFiles()
    {
        $overrideItems = [];

        $items = $this->readFromFile($this->configPath());

        $path = $this->configPath() . DIRECTORY_SEPARATOR . $this->environment();
        if (file_exists($path)) {
            $overrideItems = $this->readFromFile($path);
        }

        $items = array_merge($items, $overrideItems);
        return $items;
    }

    /**
     * @param $path
     * @return array
     */
    private function readFromFile($path)
    {
        $items = [];
        Explorer::find()->files("*.php")->in($path)->map(
            function ($key, $fileInfo) use (&$items) {
                /** @var \SplFileInfo $fileInfo */
                if ($path = $fileInfo->getPathname()) {
                    $key = str_replace('.php', '', $key);
                    $items[$key] = require($path);
                }
            }
        );

        return $items;
    }

    /**
     * @inheritdoc
     */
    public function configPath()
    {
        if (!$this->configPath) {
            $this->configPath = $this->getAbsolutePath('/config');
        }
        return $this->configPath;
    }

    /**
     * @inheritDoc
     */
    public function environment()
    {
        return $this->environment;
    }

    /**
     * Bootstrap Router
     */
    public function bootstrapRouter()
    {
        $this->getRouter()->bootstrap();
    }

    /**
     * @return Router
     * @throws \ErrorException
     */
    public function getRouter()
    {
        if (!isset($this->router)) {
            $this->router = $this->get('Router');
        }
        return $this->router;
    }

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->register('Router', $router);
        $this->router = $router;
    }

    /**
     * @param Config $config
     */
    public function loadSubscribers(Config $config)
    {
        $subscribers = $config->get('app.subscribers', []);
        foreach ($subscribers as $subscriber) {
            if ($subscriber instanceof Subscriber) {
                $subscriber->subscribe($this->getDispatcher());
            }
        }
    }

    /**
     * @param Config $config
     */
    public function loadServices(Config $config)
    {
        $services = array_merge($this->components, $config->get('services', []));
        $this->registerComponents($services);
    }

    /**
     * @return array
     */
    public function getCachedConfigItems()
    {
        $file = md5('framework.conf') . '.php';
        if (file_exists($this->getAbsolutePath('/storage/framework/cache/' . $file))) {
            return $this->getCache()->get('framework.conf');
        }

        return [];
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = $this->get('Cache');
        }
        return $this->cache;
    }

    /**
     * @param Config $config
     */
    public function cacheConfig(Config $config)
    {
        $this->getCache()->put('framework.conf', $config->all(), $config->get('app.ttl', 60));
    }

    /**
     * @inheritdoc
     */
    public function getFileSystem()
    {
        if ($this->fileSystem) {
            return $this->fileSystem;
        }

        return $this->fileSystem = $this->get('FileSystem');
    }

    /**
     * @param $variable
     * @param $value
     */
    public function set($variable, $value)
    {
        $this->{$variable} = $value;
    }

    /**
     * @inheritDoc
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function name()
    {
        return $this->applicationName;
    }

    /**
     * @inheritdoc
     */
    public function appPath()
    {
        return $this->appPath;
    }

    /**
     * @inheritdoc
     */
    public function cachePath()
    {
        if (!$this->cachePath) {
            $path = $this->getConfig()->get('app.cachePath', '/storage/framework/cache');
            $cachePath = $this->getAbsolutePath($path);
            $this->cachePath = $this->getAbsolutePath($cachePath);
        }
        return $this->cachePath;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if (!isset($this->config)) {
            $this->config = $this->get('Config');
        }
        return $this->config;
    }

    /**
     * @inheritdoc
     */
    public function publicFolder()
    {
        if (!$this->docRoot) {
            $path = $this->getConfig()->get('app.publicPath', '/web');
            $publicPath = $this->getAbsolutePath($path);
            $this->docRoot = $this->getAbsolutePath($publicPath);
        }
        return $this->docRoot;
    }

    /**
     * @inheritdoc
     */
    public function showMaintenance()
    {
        require $this->getAbsolutePath($this->storagePath() . '/framework/down.php');
        $this->terminate();
        exit;
    }

    /**
     * @inheritdoc
     */
    public function storagePath()
    {
        if (!$this->storagePath) {
            $path = $this->getConfig()->get('app.storagePath', '/storage');
            $storagePath = $this->getAbsolutePath($path);
            $this->storagePath = $this->getAbsolutePath($storagePath);
        }
        return $this->storagePath;
    }

    /**
     * Dispatch application termination
     */
    public function terminate()
    {
        $this->dispatch('core.app.terminate', $this);
    }

    /**
     * @return ResponseInterface|object
     * @throws \ErrorException
     */
    public function getResponse()
    {
        if (!isset($this->response)) {
            $this->response = $this->get('Response');
        }
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return View|object
     * @throws \ErrorException
     */
    public function getView()
    {
        if (!isset($this->view)) {
            $this->view = $this->get('View');
        }
        return $this->view;
    }

    /**
     * @param View $view
     */
    public function setView(View $view)
    {
        $this->view = $view;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->dispatch('core.app.run.pre', $this);
        $this->response = $response = $this->handle($this->getRequest());
        $response->send();
        $this->terminate();
    }

    /**
     * @param RequestInterface $request
     * @return array|mixed
     */
    protected function handle(RequestInterface $request)
    {
        $response = $this->dispatch('core.app.handle.pre', $this);

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        return $this->parseRequest($request);
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    protected function parseRequest(RequestInterface $request)
    {
        $router = $this->getRouter();
        $response = $router->handle($request);
        $response = $this->buildResponse($response, $request);
        return $response;
    }

    /**
     * @param $response
     * @param RequestInterface $request
     * @return Response|void
     */
    protected function buildResponse($response, RequestInterface $request)
    {
        if ($response instanceof View) {
            $response = new Response($response->fetch());
        } elseif (!$response instanceof ResponseInterface) {
            $response = new Response($response);
        }

        $response->format($request);

        return $response;
    }

    /**
     * @return RequestInterface
     * @throws \ErrorException
     */
    public function getRequest()
    {
        if (!isset($this->request)) {
            if ($this->serviceExists('Request')) {
                $this->request = $this->get('Request');
            } else {
                $this->setRequest(Request::createFromGlobals());
                $this->register('Request', $this->request);
            }
        }
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->register('Request', $request);
        $this->request = $request;
    }

    /**
     * @param Application $application
     * @return false|mixed
     * @throws \ErrorException
     */
    public function preHandle(Application $application)
    {
        $request = $application->getRequest();
        $application->setCacheKeys($request);
        $cache = $application->getCache();

        if ($cache->exists($application->getCacheKey('response')) && !$request->isAjax()) {
            return $cache->get($application->getCacheKey('response'));

        } elseif ($cache->exists($application->getCacheKey('route'))
            && $application->getConfig()->get('app.cacheRoutes', true)
        ) {
            $route = $cache->get($application->getCacheKey('route'));
            if ($route instanceof Route) {
                return $this->getRouter()->run($route);
            }
        }

        return false;
    }

    /**
     * Generates and sets Cache Keys for current Route
     *
     * @param RequestInterface $request
     */
    public function setCacheKeys(RequestInterface $request)
    {
        $path = $request->getPath();
        $this->cacheKeys['route'] = md5($path . '_route_' . session_id());
        $this->cacheKeys['response'] = md5($path . '_response_' . session_id());
    }

    /**
     * @param $name
     * @return mixed
     * @throws \ErrorException
     */
    public function getCacheKey($name)
    {
        if (isset($this->cacheKeys[$name])) {
            return $this->cacheKeys[$name];
        }

        return false;
    }

    /**
     * Caches the current Route
     *
     * @param Router $router
     */
    public function cacheRoute(Router $router)
    {
        $route = $router->getCurrentRoute();
        if (!is_null($route) && !$this->request->isAjax() && $route->isCacheable()) {
            $this->getCache()->put($this->getCacheKey('route'), $route, $this->getConfig()->get('ttl'));
        }
    }

    /**
     * @inheritdoc
     */
    public function getMapper($type = null)
    {
        $dbConfig = $this->config->getDatabase();
        if (is_null($type)) {
            $type = $dbConfig['default'];
        }

        if ($this->mappers[$type] instanceof Mapper) {
            return $this->mappers[$type];
        }

        if (isset($dbConfig['connections'][$type]['mapper'])) {
            $mapperClass = $dbConfig['connections'][$type]['mapper'];
        } elseif (isset($dbConfig['connections'][$type]) && $type === 'mysql') {
            $mapperClass = $this->mappers['mysql'];
        } else {
            throw new \LogicException('Database Mapper not provided');
        }

        if ($mapperClass instanceof Mapper) {
            return $mapperClass;
        }

        return $this->mappers[$type] = new $mapperClass($dbConfig['connections'][$type]);
    }

    /**
     * Application auto loader
     *
     * @param $class
     */
    public function autoload($class)
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

        $realPath = $this->getRealPath($classFile);

        if (!is_readable($realPath)) {
            return;
        }

        include $realPath;
    }
}
