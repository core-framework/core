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

use Core\Contracts\Events\Dispatcher;
use Core\Contracts\Reactor\Runnable;
use Core\Contracts\Request\Request;
use Core\Contracts\Router\Router;

interface Application extends Runnable
{
    /**
     * Get Application Version
     *
     * @return string
     */
    public function version();

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
     * Boot Application Services
     *
     * @return void
     */
    public function boot();

    /**
     * Dispatch Application Event
     *
     * @param $event
     * @param array $payload
     * @return array|mixed
     */
    public function dispatch($event, $payload = []);

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
     * @param $definition
     * @param null $arguments
     * @param null $name
     * @return array|mixed
     */
    public function build($definition, $arguments = null, $name = null);

    /**
     * @return array
     */
    public function getCachedConfigItems();
}