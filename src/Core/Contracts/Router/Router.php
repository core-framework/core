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

namespace Core\Contracts\Router;


use Core\Contracts\Request\Request;
use Core\Contracts\Response\Response;
use Core\Exceptions\PageNotFoundException;

interface Router
{
    /**
     * Bootstrap Router
     *
     * @return void
     */
    public function bootstrap();

    /**
     * Load defined routes
     *
     * @return void
     */
    public function loadRoutes();

    /**
     * Cache Routes
     *
     * @return bool
     */
    public function cacheRoutes();

    /**
     * Use aesthetic routing (/{controller}/{method}/[{argument1}/..])
     *
     * @param bool $bool
     * @return void
     */
    public function useAestheticRouting($bool = true);

    /**
     * Determine if Aesthetic Routing is set
     *
     * @return bool
     */
    public function isAestheticRouting();

    /**
     * Get Controller namespace
     *
     * @return mixed
     */
    public function getControllerNamespace();

    /**
     * Get Request
     *
     * @return Request
     */
    public function getRequest();
    
    /**
     * Set Path prefix
     *
     * @param $prefix
     * @return $this
     */
    public function setPrefix($prefix);
    
    /**
     * Get Path prefix
     *
     * @return mixed
     */
    public function getPrefix();
    
    /**
     * Get current Route
     *
     * @return Route
     */
    public function getCurrentRoute();
    
    /**
     * Set current Route
     *
     * @param Route $currentRoute
     */
    public function setCurrentRoute($currentRoute);
    
    /**
     * Get defined Routes
     *
     * @param null $method
     * @return array|mixed
     */
    public function getRoutes($method = null);
    
    /**
     * Add route to routes (collection)
     *
     * @param $uri
     * @param $methods
     * @param $action
     * @param array $options
     * @return $this
     */
    public function addRoute($uri, $methods, $action, $options = []);

    /**
     * Add GET Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function get($uri, $action, $options = []);

    /**
     * Add POST Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function post($uri, $action, $options = []);

    /**
     * Add PUT Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function put($uri, $action, $options = []);

    /**
     * Add PATCH Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function patch($uri, $action, $options = []);

    /**
     * Add DELETE Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function delete($uri, $action, $options = []);

    /**
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function options($uri, $action, $options = []);

    /**
     * Add ALL (GET, POST, PUT, PATCH, DELETE) Route to routes (collection)
     *
     * @param $uri
     * @param $action
     * @param array $options
     * @return Router
     */
    public function any($uri, $action, $options = []);
    
    /**
     * Group Route(s) together
     *
     * @param array $options
     * @param \Closure $callback
     */
    public function group($options = [], \Closure $callback);
    
    /**
     * Set current Router options (for current route group)
     * 
     * @param array $options
     */
    public function setOptions(array $options);
    
    /**
     * Delete(reset) Router options
     */
    public function deleteOptions();
    
    /**
     * Handle Request
     *
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request);
    
    /**
     * Create Response from Exception
     *
     * @param \Exception $exception
     * @return Response
     */
    public function makeExceptionResponse(\Exception $exception);
    
    /**
     * Run Route parsed by Router
     *
     * @param Route $route
     * @return mixed
     * @throws \HttpRuntimeException
     */
    public function run(Route $route);
    
     /**
     * Parse Request to get Matching Route
     *
     * @param Request $request
     * @return mixed|null
     * @throws PageNotFoundException
     */
    public function parse(Request $request);
}