<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 19/03/16
 * Time: 6:38 PM
 */

namespace Core\Router;


use Core\Config\Config;
use Core\Contracts\MiddlewareContract;
use Core\Exceptions\ControllerMethodNotFoundException;
use Core\Exceptions\ControllerNotFoundException;
use Core\Exceptions\PageNotFoundException;
use Core\Foundation\DataCollection;
use Core\Request\Request;
use Core\Response\Response;

class RouterKernel
{
    protected $currentRoute;

    protected $routes = [];

    protected $middleware = [];

    protected $request;

    protected $basePath;

    protected $appPath;

    protected $currentOptions = [];

    protected $config = [
        'controllerNamespace' => 'app\Controllers',
        'cacheRoutes' => true,
        'useAestheticRouting' => false
    ];

    protected static $instance;

    /**
     * Router constructor.
     *
     * @param $basePath
     * @param $config
     */
    public function __construct($basePath = null, $config = [])
    {
        static::$instance = $this;

        if (!is_null($basePath)) {
            $this->setBasePath($basePath);
            $this->setAppPath(rtrim($basePath, '/') . '/app');
            $this->bootstrap();
        }

        $this->loadConfig($config);
    }

    /**
     * Load routes from routes file
     */
    public function bootstrap()
    {
        $file = $this->getAppPath() . '/routes.php';
        if (is_readable($file)) {
            require($file);
        }
    }

    /**
     * Load Configs into Router
     *
     * @param array $config
     */
    protected function loadConfig($config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge_recursive($this->config, $config);
            $this->loadRoutes();
        }
    }

    /**
     * Set whether to use Aesthetic Routing (/{controller}/{method}/{param1}/{param2})
     *
     * @param bool $bool
     */
    public function useAestheticRouting($bool = true)
    {
        $this->config['useAestheticRouting'] = boolval($bool);
    }

    /**
     * Determine if Aesthetic Routing is set
     *
     * @return mixed
     */
    public function isAestheticRouting()
    {
        return $this->config['useAestheticRouting'];
    }

    /**
     * Get Controller namespace
     *
     * @return mixed
     */
    public function getControllerNamespace()
    {
        return $this->config['controllerNamespace'];
    }

    /**
     * Get Request
     *
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Load routes from Config
     */
    public function loadRoutes()
    {

    }

    /**
     * Set Router instance
     *
     * @param $instance
     */
    public static function setInstance($instance)
    {
        static::$instance = $instance;
    }

    /**
     * Get Router Instance
     *
     * @param null $basePath
     * @param array $config
     * @return mixed
     */
    public static function getInstance($basePath = null, $config = [])
    {
        if (!static::$instance) {
            static::$instance = new static($basePath, $config);
        }

        return static::$instance;
    }

    /**
     * Get New Instance of a Router
     *
     * @param null $basePath
     * @param array $config
     * @return static
     */
    public static function getNewInstance($basePath = null, $config = [])
    {
        return static::$instance = new static($basePath, $config);
    }

    /**
     * set base path
     *
     * @param $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * Get base path
     *
     * @return mixed
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param $appPath
     * @return $this
     */
    public function setAppPath($appPath)
    {
        $this->appPath = $appPath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppPath()
    {
        return $this->appPath;
    }

    /**
     * Set Path prefix
     *
     * @param $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->currentOptions['prefix'] = $prefix;
        return $this;
    }

    /**
     * Get Path prefix
     *
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->currentOptions['prefix'];
    }

    /**
     * Get current Route
     *
     * @return Route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    /**
     * Set current Route
     *
     * @param Route $currentRoute
     */
    public function setCurrentRoute($currentRoute)
    {
        $this->currentRoute = $currentRoute;
    }

    /**
     * Get defined Routes
     *
     * @param null $method
     * @return array|mixed
     */
    public function getRoutes($method = null)
    {
        if (is_null($method)) {
            return $this->routes;
        }

        if (isset($this->routes[strtoupper($method)])) {
            return $this->routes[strtoupper($method)];
        } else {
            throw new PageNotFoundException;
        }
    }

    /**
     * Add route to routes (collection)
     *
     * @param $uri
     * @param $methods
     * @param $action
     * @param array $options
     * @return $this
     */
    protected function addRoute($uri, $methods, $action, $options = [])
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        if (!empty($this->currentOptions)) {
            $options = array_merge($options, $this->currentOptions);
        }

        foreach ($methods as $i => $method) {
            $this->routes[$method][$uri] = new Route($uri, $methods, $action, $options);
        }

        return $this;
    }

    /**
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public static function get($uri, $action, $options = [])
    {
        /** @var Router $router */
        $router = static::getInstance();
        return $router->addRoute($uri, ['GET'], $action, $options);
    }

    /**
     * Add POST Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public static function post($uri, $action, $options = [])
    {
        /** @var Router $router */
        $router = static::getInstance();
        return $router->addRoute($uri, ['POST'], $action, $options);
    }

    /**
     * Add PUT Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public static function put($uri, $action, $options = [])
    {
        /** @var Router $router */
        $router = static::getInstance();
        return $router->addRoute($uri, ['PUT'], $action, $options);
    }

    /**
     * Add PATCH Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public static function patch($uri, $action, $options = [])
    {
        /** @var Router $router */
        $router = static::getInstance();
        return $router->addRoute($uri, ['PATCH'], $action, $options);
    }

    /**
     * Add DELETE Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public static function delete($uri, $action, $options = [])
    {
        /** @var Router $router */
        $router = static::getInstance();
        return $router->addRoute($uri, ['DELETE'], $action, $options);
    }

    /**
     * Add ALL (GET, POST, PUT, PATCH, DELETE) Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public static function any($uri, $action, $options = [])
    {
        /** @var Router $router */
        $router = static::getInstance();
        return $router->addRoute($uri, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $action, $options);
    }

    /**
     * Group Route(s) together
     *
     * @param array $options
     * @param \Closure $callback
     */
    public static function group($options = [], \Closure $callback)
    {
        /** @var Router $router */
        $router = static::getInstance();
        $router->setOptions($options);
        call_user_func($callback, $router);
        //$router->setPrefix(null);
        $router->deleteOptions();
    }

    public function setOptions(array $options)
    {
        $this->currentOptions = $options;
    }

    public function deleteOptions()
    {
        $this->currentOptions = [];
    }
    
    protected function parseOptions()
    {
        DataCollection::each($this->currentOptions, function($key, $value) {
            
        });
    }

    // Request Handling
    /**
     * Handle Request
     *
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        try {
            $route = $this->parse($request);
            $response = $this->run($route);
        } catch (\Exception $e) {
            $response = $this->makeExceptionResponse($e);
        }

        return $response;
    }

    /**
     * Create Response from Exception
     *
     * @param \Exception $exception
     * @return Response
     */
    public function makeExceptionResponse(\Exception $exception)
    {
        return new Response($exception->getMessage(), $exception->getCode());
    }

    /**
     * Run Route parsed by Router
     *
     * @param Route $route
     * @return mixed
     * @throws \HttpRuntimeException
     */
    public function run(Route $route)
    {
        $next = $this->getNextCallable($route);
        if ($route->hasMiddleware()) {
            $middleware = $route->getMiddleware();
            return $this->executeMiddleware($middleware, $this->getInstance(), $next);
        }

        return $next();
    }

    /**
     * Executes bound middleware
     *
     * @param $middleware
     * @param Router $router
     * @param \Closure $next
     * @return mixed
     */
    protected function executeMiddleware($middleware, Router $router, \Closure $next)
    {
        if (class_exists($middleware, true)) {
            $middlewareObj = new $middleware();
            if (!$middlewareObj instanceof MiddlewareContract) {
                throw new \RuntimeException("Given Middleware does not comply with the MiddlewareContract!", 600);
            }
            return $middlewareObj->run($router, $next);
        }

        throw new \RuntimeException("Middleware - {$middleware} not found.", 604);
    }

    protected function getControllerArgs()
    {
        return [$this->getBasePath(), $this->getInstance(), Config::get()];
    }

    /**
     * Get next executions as Callable
     *
     * @param Route $route
     * @return \Closure
     * @throws \HttpRuntimeException
     */
    protected function getNextCallable(Route $route)
    {
        $controller = $route->getController();
        $classMethod = $route->getClassMethod();
        $namespace = $this->getControllerNamespace();
        $payload = $route->getParameterValues();
        $args = $this->getControllerArgs();
        
        if (is_callable($controller)) {
            $next = function () use ($controller, $payload) {
                return $controller($payload);
            };
        } else {
            $class = $namespace . '\\' . $controller;
            if (class_exists($class, true)) {
                $next = function () use ($class, $args, $classMethod, $payload) {
                    return $this->runController($this->makeController($class, $args), $classMethod, $payload);
                };
            } else {
                throw new ControllerNotFoundException;
            }
        }

        return $next;
    }

    public function makeController($class, array $args)
    {
        $reflection = new \ReflectionClass($class);
        return $reflection->newInstanceArgs($args);
    }

    protected function runController($obj, $classMethod, $payload = [])
    {
        if (!method_exists($obj, $classMethod)) {
            throw new ControllerMethodNotFoundException;
        }
        return $obj->{$classMethod}($payload);
    }

    /**
     * Parse Request to get Matching Route
     *
     * @param Request $request
     * @return mixed|null
     * @throws PageNotFoundException
     */
    public function parse(Request $request)
    {
        $this->request = $request;
        $method = $request->getHttpMethod();
        $routes = $this->getRoutes($method);
        $route = $this->findRoute($routes, $request);
        $this->setCurrentRoute($route);
        return $route;
    }

    /**
     * Find matching Route from Route(s)
     * 
     * @param array $routes
     * @param Request $request
     * @return mixed|null
     * @throws PageNotFoundException
     */
    protected function findRoute(array $routes, Request $request)
    {
        $path = $request->getPath();
        if (isset($routes[$path])) {
            $route = $routes[$path];
        } else {
            $route = DataCollection::find(
                $routes,
                function ($key, $value) use ($request) {
                    return $value->isMatch($request);
                }
            );
        }

        if (!$route instanceof Route) {
            throw new PageNotFoundException;
        }

        return $route;
    }
    
    public function addPrefix($prefix, $uri)
    {
        $uri = '/' . trim($prefix, '/') . '/' . ltrim($uri, '/');
        return $uri;
    }
}