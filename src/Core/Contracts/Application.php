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

namespace Core\Contracts;

use Core\Contracts\Database\Mapper;
use Core\Contracts\Events\Dispatcher;
use Core\Contracts\FileSystem\FileSystem;
use Core\Contracts\Reactor\Runnable;
use Core\Contracts\Request\Request;
use Core\Contracts\Response\Response;
use Core\Contracts\Router\Router;

interface Application extends Runnable
{

    /**
     * @param $name
     * @return mixed
     */
    public static function get($name);

    /**
     * Method to register services
     *
     * @param string $name
     * @param Callable|\Closure|string $definition
     * @param bool $shared
     * @return mixed
     * @throws \ErrorException
     */
    public static function register($name, $definition, $shared = true);

    /**
     * Finds the instance of the given (namespaced) Class
     *
     * @param string|object $namespacedClass
     * @param bool|mixed $fail
     * @return bool|mixed
     */
    public static function findInstance($namespacedClass, $fail = false);

    /**
     * Get Application Version
     *
     * @return string
     */
    public function version();

    /**
     * Get Application name
     *
     * @return string
     */
    public function name();

    /**
     * Returns true if application is currently running from CLI (console)
     *
     * @return bool
     */
    public function isCLI();

    /**
     * Set current application version
     *
     * @param $version
     * @return $this
     */
    public function setVersion($version);

    /**
     * Get Application Base Path
     *
     * @return string
     */
    public function basePath();

    /**
     * Gets App folder Path
     *
     * @return string
     */
    public function appPath();

    /**
     * Gets Config folder Path
     *
     * @return string
     */
    public function configPath();

    /**
     * Gets Cache folder Path
     *
     * @return string
     */
    public function cachePath();

    /**
     * Gets Storage folder Path
     *
     * @return string
     */
    public function storagePath();

    /**
     * Returns the public folder path
     *
     * @return string
     */
    public function publicFolder();

    /**
     * @param null $environment
     * @return mixed
     */
    public function setEnvironment($environment = null);

    /**
     * Get current Environment state
     *
     * @return string
     */
    public function environment();

    /**
     * Check if Application is Down
     *
     * @return bool
     */
    public function isDown();

    /**
     * Show (Serve) Maintenance file
     *
     * @return mixed
     */
    public function showMaintenance();

    /**
     * Boot Application Services
     *
     * @return void
     */
    public function boot();

    /**
     * Terminat
     *
     * @return void
     */
    public function terminate();

    /**
     * Dispatch Application Event
     *
     * @param $event
     * @param array $payload
     * @return array|mixed
     */
    public function dispatch($event, $payload = []);

    /**
     * Get complete path relative to base/root path
     *
     * @param $relativePath
     * @return string
     */
    public function getAbsolutePath($relativePath);

    /**
     * @return Config
     */
    public function getConfig();

    /**
     * @return Cache
     */
    public function getCache();

    /**
     * @return Router
     * @throws \ErrorException
     */
    public function getRouter();

    /**
     * @return Request
     * @throws \ErrorException
     */
    public function getRequest();

    /**
     * @return Response|object
     * @throws \ErrorException
     */
    public function getResponse();

    /**
     * @return View
     * @throws \ErrorException
     */
    public function getView();

    /**
     * Generates and sets Cache Keys for current Route
     *
     * @param Request $request
     */
    public function setCacheKeys(Request $request);

    /**
     * @param $name
     * @return mixed
     * @throws \ErrorException
     */
    public function getCacheKey($name);

    /**
     * @return Dispatcher|object
     * @throws \ErrorException
     */
    public function getDispatcher();

    /**
     * @return FileSystem|object
     */
    public function getFileSystem();

    /**
     * @param $definition
     * @param null $arguments
     * @param null $name
     * @return array|mixed
     */
    public function build($definition, $arguments = null, $name = null);

    /**
     * @return array $item
     */
    public function loadConfigFromFiles();

    /**
     * @return array
     */
    public function getCachedConfigItems();

    /**
     * Return Database Mapper Object
     *
     * @param string $type
     * @return Mapper
     */
    public function getMapper($type = 'mysql');
}