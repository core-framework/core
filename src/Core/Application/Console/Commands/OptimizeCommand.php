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


namespace Core\Application\Console\Commands;


use Core\Application\Console\Command;
use Core\Contracts\Application\Console\IOStream;
use Symfony\Component\Console\Input\ArrayInput;
use Composer\Console\Application;

class OptimizeCommand extends Command
{
    public function init()
    {
        $this->setName('optimize');
        $this->setDescription('Optimizes the Core Framework Application');
        $this->addOption('all', 'a', 'Flag to run composer optimizations as well');
    }

    public function execute(IOStream $io)
    {
        $io->writeln('Optimizing Core Framework...', 'green');

        if ($this->getOptions('all') || $this->getOptions('a')) {
            putenv('COMPOSER_HOME=' . $this->application()->basePath() . '/vendor/bin/composer');

            $input = new ArrayInput(['command' => 'clearcache']);
            $composer = new Application();
            $composer->setAutoExit(false);
            $composer->run($input);

            $input = new ArrayInput(['command' => 'dumpautoload']);
            $composer = new Application();
            $composer->setAutoExit(false);
            $composer->run($input);
        }

        $cache = $this->application()->getCache();
        $config = $this->application()->getConfig();
        $router = $this->application()->getRouter();

        $cache->delete('framework.conf');
        $cache->delete('routes');

        $router->loadRoutes();
        $router->cacheRoutes();
        $cache->put('framework.conf', $config->all(), $config->get('app.ttl', 60));
        $io->writeln('Core Framework Application Optimized successfully!', 'green');
    }
}