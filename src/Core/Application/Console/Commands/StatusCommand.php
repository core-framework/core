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


use Composer\Console\Application;
use Core\Application\Console\Command;
use Core\Contracts\Application\Console\IOStream;
use Symfony\Component\Console\Input\ArrayInput;

class StatusCommand extends Command
{
    /**
     * @var IOStream
     */
    private $io;

    public function init()
    {
        $this->setName('status');
        $this->setDescription('Check if framework requirements have been met');
    }

    public function execute(IOStream $io)
    {
        $this->io = $io;
        $this->checkComposer();
        $this->checkFolderPermissions();
        $this->checkBower();
    }

    private function checkComposer()
    {
        $this->io->writeln("Checking Composer status:", 'green');
        putenv('COMPOSER_HOME=' . $this->application()->basePath() . '/vendor/bin/composer');

        $input = new ArrayInput([
            'command' => 'update',
            '--dry-run' => true
        ]);
        $composer = new Application();
        $composer->setAutoExit(false);
        $composer->run($input);
        $this->io->writeln();
    }

    private function checkFolderPermissions()
    {
        $storageSubFolders = ['/framework', '/framework/cache', '/logs', '/smarty_cache', '/smarty_cache/cache', '/smarty_cache/config', '/smarty_cache/configs', '/smarty_cache/templates_c'];
        $this->io->writeln('Checking folder permissions:', 'green');
        $fileSystem = $this->application()->getFileSystem();

        if ($fileSystem->isWritable($this->application()->storagePath())) {
            $this->io->writeln('storage folder permissions OK', 'green');
            foreach ($storageSubFolders as $subFolder) {
                if ($fileSystem->isWritable($this->application()->storagePath() . $subFolder)) {
                    $this->io->writeln("storage{$subFolder} folder permissions OK", 'green');
                } else {
                    $this->io->showWarning("storage{$subFolder} folder is not writable or missing correct permissions");
                }
            }
        } else {
            $this->io->showWarning('storage folder is not writable or missing correct permissions');
        }
        $this->io->writeln();
    }

    private function checkBower()
    {
        $this->io->writeln('Checking Node.js (npm) and Bower status:');
        if (!$this->checkIsInstalled('npm')) {
            $this->io->showWarning('Node.js Package Manager (npm) is missing! Node.js Package Manager is needed to install/run bower, a powerful tool for front-end dependency management');
            return false;
        }
        if (!$this->checkIsInstalled('bower')) {
            $this->io->showWarning('bower is missing! bower is a powerful tool for front-end dependency management');
            return false;
        }
        system('bower list');
        $this->io->writeln();
        return true;
    }

    private function checkIsInstalled($command)
    {
        $string = trim(shell_exec("{$command} -v"));
        if (preg_match('/^(\w+\s)?(\d+\.)?(\d+\.)?(\*|\d+)$/', $string)) {
            return true;
        } else {
            return false;
        }
    }

}