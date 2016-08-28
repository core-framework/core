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

use Core\Container\Container;
use Core\Contracts\Application as ApplicationInterface;
use Core\Contracts\Bootsrapper;
use Core\Contracts\Cache;
use Core\Contracts\Config;
use Core\Contracts\Events\Dispatcher;
use Core\Contracts\Events\Subscriber;
use Core\Contracts\FileSystem\FileSystem;
use Core\Contracts\Request\Request as RequestInterface;
use Core\Contracts\Response\Response;
use Core\Contracts\Router\Route;
use Core\Contracts\Router\Router;
use Core\Contracts\View;
use Core\Request\Request;

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
     * Application Document Root
     *
     * @var
     */
    private $docRoot;

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
    protected $alias = [
        '@web' => '@base/web',
        '@app' => '@base/app'
    ];

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
     * Application default mapper
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
     * @var Response $response
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
        $this->setAppPath();
        $this->setDocumentRoot();
    }

    /**
     * Sets base path and registers it as Alias (@base) in Application
     *
     * @param $path
     * @return $this
     */
    public function setBasePath($path)
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

    public function setAppPath($appPath = null)
    {
        $appPath = rtrim($appPath, "/");
        $this->appPath = empty($appPath) ? $this->basePath . "/app" : $appPath;
        $this->addAlias('@app', $this->appPath);
        return $this;
    }

    /**
     * Set current Document Root
     *
     * @param null $docRoot
     * @return $this
     */
    public function setDocumentRoot($docRoot = null)
    {
        $docRoot = rtrim($docRoot, "/");
        $this->docRoot = is_null($docRoot) ? $this->basePath . "/web" : $docRoot;
        $this->addAlias("@docRoot", $this->docRoot);
        return $this;
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
        $this->loadBaseComponents();
        $this->bootstrap();
    }

    public function checkIfAppIsDown()
    {
        if ($this->isDown() && !$this->isCLI()) {

        }
    }

    /**
     * @return array
     */
    protected function loadBaseComponents()
    {
        return $this->loadComponents($this->coreComponents);
    }

    /**
     * @param $components
     * @return array
     */
    public function loadComponents($components)
    {
        $responses = [];
        foreach($components as $name => $component) {
            if ($this->event) {
                $responses['core.app.'.strtolower($name).'.preload'] = $this->dispatch('core.app.'.strtolower($name).'.preload', static::$app);
            }
            if (is_array($component)) {
                $this->register($name, $component[0])->setArguments($this->parseArguments($component[1]));
            } else {
                $this->register($name, $component);
            }
            $responses['core.app.'.strtolower($name).'.postload'] = $this->dispatch('core.app.'.strtolower($name).'.postload', static::$app);
        }
        return $responses;
    }

    /**
     * @param $arguments
     * @return mixed
     */
    public function parseArguments($arguments)
    {
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        foreach($arguments as &$argument) {
            if (is_string($argument) && strContains('@', $argument)) {
                $argument = $this->getRealPath($argument);
            }
        }

        return $arguments;
    }

    /**
     * Bootstrap application
     */
    protected function bootstrap()
    {
        foreach ($this->bootstrappers as $bootstrapper)
        {
            /** @var Bootsrapper $bootable */
            $bootable = new $bootstrapper();
            $bootable->bootstrap($this);
        }
    }

    /**
     * @param $definition
     * @param null $arguments
     * @param null $name
     * @return array|mixed
     */
    public function build($definition, $arguments = null, $name = null)
    {
        $this->{strtolower($name)} = $this->make($definition, $arguments, $name);
        return $this->dispatch('core.app.'.strtolower($name).'.booted', $this->{strtolower($name)});
    }

    /**
     * Subscribe to Application events
     *
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->on('core.app.config.booted', [$this, 'loadSubscribers'], 0);
        $dispatcher->on('core.app.config.booted', [$this, 'loadServices'], 1);
        $dispatcher->on('core.app.config.booted', [$this, 'cacheConfig'], 2);
        $dispatcher->on('core.app.router.postload', [$this, 'bootstrapRouter'], 0);
        $dispatcher->on('core.app.handle.pre', [$this, 'preHandle'], 0);
        $dispatcher->on('core.router.matched', [$this, 'cacheRoute'], 0);
    }

    /**
     * Bootstrap Router
     */
    public function bootstrapRouter()
    {
        $this->getRouter()->bootstrap();
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
        $this->loadComponents($services);
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
     * @param Config $config
     */
    public function cacheConfig(Config $config)
    {
        $this->getCache()->put('framework.conf', $config->all(), $config->get('app.ttl', 60));
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
     * Set Application Environment
     */
    public function setEnvironment()
    {
        if (getenv('_environment') === static::TESTING_STATE) {
            $this->environment = static::TESTING_STATE;
            putenv('environment=' . static::TESTING_STATE);
        } else {
            $env = !isset($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? static::DEVELOPMENT_STATE : static::PRODUCTION_STATE;
            $this->environment = $env;
            putenv('environment=' . $env);
        }

        return $this;
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

    public function name()
    {
        return $this->applicationName;
    }

    public function isCLI()
    {
        return (php_sapi_name() === 'cli');
    }

    /**
     * @inheritDoc
     */
    public function basePath()
    {
        return $this->basePath;
    }

    public function appPath()
    {
        return $this->appPath;
    }

    public function configPath()
    {
        return $this->getAbsolutePath('/config');
    }

    public function cachePath()
    {
        return $this->getAbsolutePath('/storage/framework/cache');
    }

    public function storagePath()
    {
        return $this->getAbsolutePath('/storage');
    }

    public function publicFolder()
    {
        $publicFolderName = $this->getConfig()->get('app.publicFolderName', 'web');
        return $this->getAbsolutePath('/'.$publicFolderName);
    }

    /**
     * @inheritDoc
     */
    public function environment()
    {
        return $this->environment;
    }

    /**
     * @inheritDoc
     */
    public function isDown()
    {
        return file_exists($this->getAbsolutePath('/storage/framework/down.php'));
    }

    public function showMaintenance()
    {
        require $this->getAbsolutePath('/storage/framework/down.php');
        $this->terminate();
        exit;
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
     * @return Response|object
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
     * @param Response $response
     */
    public function setResponse(Response $response)
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

        if ($response instanceof Response) {
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
        return $response;
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
        
        if ($cache->exists($application->getCacheKey('response')) && !$request->isAjax())
        {
            return $cache->get($application->getCacheKey('response'));

        } elseif ($cache->exists($application->getCacheKey('route'))
            && $application->getConfig()->get('app.cacheRoutes',true))
        {
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

    protected function prepareResponse(Response $response)
    {

    }

    public function cacheRoute(Router $router)
    {
        $route = $router->getCurrentRoute();
        if (!is_null($route) && !$this->request->isAjax() && $route->isCacheable()) {
            $this->getCache()->put($this->getCacheKey('route'), $route, $this->getConfig()->get('ttl'));
            //$this->cache->cacheContent($this->routeKey, $route, $this->getTtl());
        }
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
        if (!is_dir($realPath) && substr($realPath,-1) != '/') {
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
     * Dispatch application termination
     */
    public function terminate()
    {
        $this->dispatch('core.app.terminate', $this);
    }
}
