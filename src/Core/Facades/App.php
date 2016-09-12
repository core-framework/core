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


namespace Core\Facades;


use Core\Reactor\Facade;
use Core\Contracts\Events\Dispatcher;
use Core\Contracts\FileSystem\FileSystem;
use Core\Contracts\Config;
use Core\Contracts\Cache;
use Core\Contracts\Router\Router;
use Core\Contracts\Request\Request;
use Core\Contracts\Response\Response;
use Core\Contracts\View;
use Core\Contracts\Database\Mapper;

/**
 * Class App
 * @package Core\Facades
 * @method static Dispatcher getDispatcher()
 * @method static FileSystem getFileSystem()
 * @method static string version()
 * @method static string name()
 * @method static bool isCLI()
 * @method static string basePath()
 * @method static string appPath()
 * @method static string configPath()
 * @method static string cachePath()
 * @method static string storagePath()
 * @method static string publicFolder()
 * @method static string environment()
 * @method static string isDown()
 * @method static string showMaintenance()
 * @method static Config getConfig()
 * @method static Cache getCache()
 * @method static Router getRouter()
 * @method static Request getRequest()
 * @method static Response getResponse()
 * @method static View getView()
 * @method static Mapper getMapper($type = 'mysql')
 *
 */
class App extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getName()
    {
        return 'App';
    }
}