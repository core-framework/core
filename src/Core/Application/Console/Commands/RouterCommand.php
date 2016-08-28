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
use Core\Application\Console\Table;
use Core\Application\Console\Validators\OptionsValidator;
use Core\Contracts\Application\Console\IOStream;
use Core\Contracts\Router\Route;

class RouterCommand extends Command
{
    public function init()
    {
        $this->setName('router');
        $this->addArgument('command', 'The router command to execute.', null)->addValidation(new OptionsValidator(
            [
                'list' => 'List all registered Routes',
                'clearcache' => 'Clear all cached Routes',
                'cache' => 'Generate fresh cache of registered Routes for better performance'
            ]
        ));
        $this->setDescription('Execute router specific commands.');
        $this->addOption('all', 'a', 'flag to show all route data in list table');
    }

    public function execute(IOStream $io)
    {
        $command = $this->input('command');
        $all = $this->options('all');
        $verbose = $this->options('verbose');

        if (!$command) {
            $io->showErr("Please specify the 'command' you want to execute. See below help for details");
            $this->showHelp($io);
            return;
        }

        if ($command === 'list') {
            $command = 'listroutes';
        }

        return call_user_func([$this, $command], $io, $verbose, $all);
    }

    public function listroutes(IOStream $io, $verbose = false, $flag = false)
    {
        $routes = $this->application()->getRouter()->getRoutes();
        $table = new Table(Table::CONSOLE_TABLE_ALIGN_LEFT);
        $header = ['Route', 'Method', 'Action', 'Middleware'];

        if ($flag) {
            $header[] = "Options";
        }

        $list = [];
        foreach ($routes as $method => $routesArr) {
            foreach ($routesArr as $path => $route) {
                $row = [];
                /** @var Route $route */
                $row[] = $route->getUri();
                $row[] = $method;
                $row[] = $route->getAction();
                $row[] = $route->getMiddleware();
                if ($flag) {
                    $this->addOptionsToTable($route, $row);
                }
                array_push($list, $row);
            }
        }

        $io->writeln($table->fromArray($header, $list));
    }

    private function addOptionsToTable(Route $route, array &$row)
    {
        $options = $route->getOptions();
        unset($options['middleware']);

        if (empty($options)) {
            $row[] = " ";
        } else {
            foreach($options as $key => $val) {
                if (is_array($val) && $key !== 'middleware') {
                    $row[] = "{$key}:" . json_encode($options[$key], JSON_PRETTY_PRINT);
                } elseif ($key !== 'middleware') {
                    $row[] = $options[$key];
                }
            }
        }
    }

    public function clearcache(IOStream $io, $verbose = true)
    {
        $cache = $this->application()->getCache();
        if ($cache->delete('routes') && $verbose) {
            $io->writeln('Cached routes cleared successfully!', 'green');
        } else {
            $io->showErr('Unable to delete cached routes');
        }
    }

    public function cache(IOStream $io, $verbose = false)
    {
        $this->clearcache($io, $verbose);
        $router = $this->application()->getRouter();
        if ($router->cacheRoutes()) {
            $io->writeln('Cached routes cleared successfully!', 'green');
        } else {
            $io->showErr('Unable to create routes cache');
        }
    }
}