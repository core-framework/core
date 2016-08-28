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

namespace Core\Router;

use Core\Contracts\Application;
use Core\Contracts\Cacheable;
use Core\Contracts\Middleware;
use Core\Exceptions\ControllerMethodNotFoundException;
use Core\Exceptions\ControllerNotFoundException;
use Core\Exceptions\PageNotFoundException;
use Core\Reactor\DataCollection;
use Core\Contracts\Router\Router as RouterInterface;
use Core\Contracts\Request\Request as RequestInterface;
use Core\Contracts\Router\Route as RouteInterface;
use Core\Contracts\Response\Response as ResponseInterface;
use Core\Response\Response;

class Router implements RouterInterface, Cacheable
{
    /**
     * @var RouteInterface $currentRoute
     */
    protected $currentRoute;

    /**
     * @var array $routes
     */
    protected $routes = [];

//    /**
//     * @var array $middleware
//     */
//    protected $middleware = [];

    /**
     * @var RequestInterface $request
     */
    protected $request;

    /**
     * @var array $currentOptions
     */
    protected $currentOptions = [];

    /**
     * @var array $defaultConfig
     */
    protected $defaultConfig = [
        'controllerNamespace' => 'app\Controllers',
        'cacheRoutes' => true,
        'useAestheticRouting' => false
    ];

    /**
     * @var Application
     */
    protected $application;

    /**
     * RouterKernel constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Load routes from routes file
     */
    public function bootstrap()
    {
        $cache = $this->application->getCache();
        if ($cache->exists('routes')) {
            /** @var Router $routerObj */
            $routerObj = $cache->get('routes');
            $this->routes = $routerObj->getRoutes();
        } else {
            $this->loadRoutes();
            //$this->loadConfig();
            $this->cacheRoutes();
        }
    }

    /**
     * Load Routes from routes file
     *
     * Important: loadRoutes must be called after Router instantiation to avoid cyclic search for a Router
     * Instance.
     */
    public function loadRoutes()
    {
        $file = $this->application->appPath() . '/Routes/routes.php';
        if (is_readable($file)) {
            require($file);
        }
    }

    /**
     * @deprecated
     * Load Configurations
     */
    public function loadConfig()
    {
        $config = $this->application->getConfig();
        foreach ($this->defaultConfig as $key => $value) {
            if (!$config->has($key)) {
                $config->set($key, $value);
            }
        }
    }

    /**
     * Cache Routes
     */
    public function cacheRoutes()
    {
        $cache = $this->application->getCache();
        return $cache->put('routes', $this);
    }

    /**
     * Set whether to use Aesthetic Routing (/{controller}/{method}/{param1}/{param2})
     *
     * @param bool $bool
     */
    public function useAestheticRouting($bool = true)
    {
        $this->application->getConfig()->set('router.useAestheticRouting', boolval($bool));
    }

    /**
     * Determine if Aesthetic Routing is set
     *
     * @return bool
     */
    public function isAestheticRouting()
    {
        return $this->application->getConfig()->get('router.useAestheticRouting');
    }

    /**
     * Get Controller namespace
     *
     * @return mixed
     */
    public function getControllerNamespace()
    {
        return $this->application->getConfig()->get('router.controller.namespace', '\\app\\Controllers');
    }

    /**
     * Get Request
     *
     * @return \Core\Contracts\Request\Request
     */
    public function getRequest()
    {
        return $this->application->getRequest();
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
    public function addRoute($uri, $methods, $action, $options = [])
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
     * Add GET Route to routes (collection)
     * 
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function get($uri, $action, $options = [])
    {
        return $this->addRoute($uri, ['GET'], $action, $options);
    }

    /**
     * Add POST Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function post($uri, $action, $options = [])
    {
        return $this->addRoute($uri, ['POST'], $action, $options);
    }

    /**
     * Add PUT Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function put($uri, $action, $options = [])
    {
        return $this->addRoute($uri, ['PUT'], $action, $options);
    }

    /**
     * Add PATCH Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function patch($uri, $action, $options = [])
    {
        return $this->addRoute($uri, ['PATCH'], $action, $options);
    }

    /**
     * Add DELETE Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function delete($uri, $action, $options = [])
    {
        return $this->addRoute($uri, ['DELETE'], $action, $options);
    }

    /**
     * Add ALL (GET, POST, PUT, PATCH, DELETE) Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function any($uri, $action, $options = [])
    {
        return $this->addRoute($uri, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $action, $options);
    }

    /**
     * Group Route(s) together
     *
     * @param array $options
     * @param \Closure $callback
     */
    public function group($options = [], \Closure $callback)
    {
        $this->setOptions($options);
        call_user_func($callback, $this);
        $this->deleteOptions();
    }

    /**
     * Set current Router options (for current route group)
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->currentOptions = $options;
    }

    /**
     * Delete(reset) Router options
     */
    public function deleteOptions()
    {
        $this->currentOptions = [];
    }

    // Request Handling
    /**
     * Handle Request
     *
     * @param RequestInterface $request
     * @return mixed
     */
    public function handle(RequestInterface $request)
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
     * @return ResponseInterface
     */
    public function makeExceptionResponse(\Exception $exception)
    {
        if ($this->getRequest()->isAjax()) {
            return new Response(['status' => 'error', 'statusCode' => $exception->getCode(), 'message' => $exception->getMessage()], $exception->getCode());
        } else {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Run Route parsed by Router
     *
     * @param RouteInterface $route
     * @return mixed
     * @throws \HttpRuntimeException
     */
    public function run(RouteInterface $route)
    {
        $next = $this->getNextCallable($route);
        if ($route->hasMiddleware()) {
            $middleware = $route->getMiddleware();
            return $this->executeMiddleware($middleware, $next);
        }

        return $next();
    }

    /**
     * Executes bound middleware
     *
     * @param $middleware
     * @param \Closure $next
     * @return mixed
     */
    protected function executeMiddleware($middleware, \Closure $next)
    {
        if (class_exists($middleware, true)) {
            $middlewareObj = new $middleware();
            if (!$middlewareObj instanceof Middleware) {
                throw new \RuntimeException("Given Middleware does not comply with the MiddlewareContract!", 600);
            }
            return $middlewareObj->run($this, $next);
        }

        throw new \RuntimeException("Middleware - {$middleware} not found.", 604);
    }

    /**
     * Get arguments to be passed to controller
     *
     * @return array
     */
    protected function getControllerArgs()
    {
        return [$this->application];
    }

    /**
     * Get next executions as Callable
     *
     * @param RouteInterface $route
     * @return \Closure
     * @throws \HttpRuntimeException
     */
    protected function getNextCallable(RouteInterface $route)
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
            if (strContains('\\', $controller) && class_exists($controller, true)) {
                $class = $controller;
            } else {
                $class = $namespace . '\\' . $controller;
            }
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

    /**
     * Spawns the controller class
     *
     * @param $class
     * @param array $args
     * @return object
     */
    protected function makeController($class, array $args)
    {
        $reflection = new \ReflectionClass($class);
        return $reflection->newInstanceArgs($args);
    }

    /**
     * Executes the (given) controller method
     *
     * @param $obj
     * @param $classMethod
     * @param array $payload
     * @return mixed
     */
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
     * @param RequestInterface $request
     * @return mixed|null
     * @throws PageNotFoundException
     */
    public function parse(RequestInterface $request)
    {
        //$this->request = $request;
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
     * @param RequestInterface $request
     * @return mixed|null
     * @throws PageNotFoundException
     */
    protected function findRoute(array $routes, RequestInterface $request)
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
        } else {
            $this->dispatch('core.router.matched', $this);
        }

        return $route;
    }

    /**
     * Alias for application dispatch method
     *
     * @param $event
     * @param array $payload
     */
    public function dispatch($event, $payload = [])
    {
        $this->application->dispatch($event, $payload);
    }

    /**
     * Adds path prefix
     *
     * @param $prefix
     * @param $uri
     * @return string
     */
    public function addPrefix($prefix, $uri)
    {
        $uri = '/' . trim($prefix, '/') . '/' . ltrim($uri, '/');
        return $uri;
    }

    public function __sleep()
    {
        return ['routes', 'currentOptions'];
    }

    public function __wakeup()
    {

    }
}