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


namespace Core\Application\Bootstrappers;

use Core\Contracts\Application;
use Core\Contracts\Bootsrapper;
use Core\FileSystem\Explorer;

class BootConfiguration implements Bootsrapper
{
    /**
     * @var Application
     */
    public $application;

    /**
     * @inheritDoc
     */
    public function bootstrap(Application $application)
    {
        $this->application = $application;
        if (!$application instanceof Application) {
            throw new \InvalidArgumentException("LoadConfiguration::run expects argument to be of type Application. {${gettype($application)}} given.");
        }

        $items = $application->getCachedConfigItems();

        if (empty($items)) {
            $items = $this->loadFromFiles();
        }

        $application->build(\Core\Config\Config::class, [$items], 'Config');
    }


    public function loadFromFiles()
    {
        $items = [];
        $app = $this->application;
        $path = $app->configPath() . DIRECTORY_SEPARATOR . $app->environment();

        if (!file_exists($path)) {
            $path = $app->configPath();
        }

        Explorer::find()->files("*.php")->in($path)->map(function($key, $fileInfo) use (&$items) {
            /** @var \SplFileInfo $fileInfo */
            if ($path = $fileInfo->getPathname()) {
                $key = str_replace('.php', '', $key);
                $items[$key] = require($path);
            }
        });

        return $items;
    }
}