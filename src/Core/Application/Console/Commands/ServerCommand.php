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
use Core\Application\Console\Validators\OptionsValidator;

class ServerCommand extends Command
{
    public function init()
    {
        $this->setName('server');
        $this->setDescription('Allows you to run Server (maintenance) specific commands.');
        $this->addArgument('commandName', "The Application command to execute. See 'server help' for a complete list of valid commands.", '', true)->mustValidate(new OptionsValidator(
            [
                'down' => 'Put application in maintenance mode',
                'up' => 'Bring application out of maintenance mode'
            ]
        ));
    }

    public function execute(IOStream $io)
    {
        $command = $this->input('commandName');
        if (!$command) {
            $io->showErr("Please specify the application command to execute:");
            $this->showHelp($io);

        }

        return call_user_func([$this, $command], $io);
    }

    public function up(IOStream $io)
    {
        $fileSystem = $this->application()->getFileSystem();
        $path = $this->application()->storagePath() . '/framework/down.php';
        if ($fileSystem->delete($path)) {
            $io->writeln("Application is up and running!", 'green');
        } else {
            $io->showErr("Unable to delete {$path}.");
        }
    }

    public function down(IOStream $io)
    {
        $fileSystem = $this->application()->getFileSystem();
        $path = $this->application()->storagePath() . '/framework/down.php';
        $stub = realpath(__DIR__ . '/../Stubs/down.stub');

        if ($fileSystem->copy($stub, $path)) {
            $io->writeln("Application is down in maintenance", 'yellow');
        } else {
            $io->showErr("Unable to create {$path}.");
        }
    }

}