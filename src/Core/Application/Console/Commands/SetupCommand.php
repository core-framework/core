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

class SetupCommand extends Command
{

    /**
     * @var IOStream
     */
    private $io;

    public function init()
    {
        $this->setName('setup');
        $this->setDescription('Sets up the framework with correct folder permissions and install other (missing) dependencies');
    }

    public function execute(IOStream $io)
    {
        $this->io = $io;
        $this->composerInstall();
        $this->setupPermissions();
        $this->databaseInstall();
        $this->bowerInstall();
        $this->io->writeln('Core Framework setup complete!', 'green');
    }

    private function composerInstall()
    {
        $this->io->writeln("Running Composer install:", 'green');
        putenv('COMPOSER_HOME=' . $this->application()->basePath() . '/vendor/bin/composer');

        $input = new ArrayInput([
            'command' => 'install'
        ]);
        $composer = new Application();
        $composer->setAutoExit(false);
        $composer->run($input);
        $this->io->writeln();
    }

    private function setupPermissions()
    {
        $this->io->writeln('Setting up correct folder permissions:', 'green');
        $this->changeFolderPermissions($this->application()->storagePath() . '/smarty_cache', 0777);
        $this->changeFolderPermissions($this->application()->storagePath() . '/framework', 0777);
    }

    private function databaseInstall()
    {
        $basePath = $this->application()->basePath();
        $env = $this->application()->environment();
        $config = $this->application()->getConfig();

        $response = $this->io->ask('Would you like to setup your MySql database?', 'yes', ['yes', 'no']);

        if ($response === 'yes') {
            $this->io->writeln("Setting up Mysql database credentials for '{$env}' environment:");
            $db = $this->io->ask('Please enter the database to use:', 'coreframework_db');
            $host = $this->io->ask('Please enter the database host address:', '127.0.0.1');
            $user = $this->io->ask('Please enter the database user:', 'root');
            $pass = $this->io->ask('Please enter the database password:', '');

            $databaseConf = $config->getDatabase();
            $default = $databaseConf['default'];
            $connection = &$databaseConf['connections'][$default];
            $connection['db'] = $db;
            $connection['host'] = $host;
            $connection['user'] = $user;
            $connection['pass'] = $pass;

            $this->createDatabaseEnvironmentFile($databaseConf, $env);
        }

        $this->io->writeln('Setting up Migrations:', 'green');
        system("{$basePath}/reactor migration:install");
        $this->io->writeln('Running default Migrations:', 'green');
        system("{$basePath}/reactor migration:run");
        $this->io->writeln('Running default Seeder:', 'green');
        system("{$basePath}/reactor seeder:run");
    }

    private function createDatabaseEnvironmentFile($dbConf, $env)
    {
        $fileSystem = $this->application()->getFileSystem();
        $filePath = $this->application()->basePath() . '/config/' . $env . '/database.php';
        $content = "<?php " . var_export($dbConf) . "?>";
        $fileSystem->write($filePath, $content);
    }

    private function bowerInstall()
    {
        if (!$this->checkIsInstalled('npm')) {
            if ($this->checkIsInstalled('brew')) {
                $this->io->writeln('Installing Node and npm:', 'green');
                system('brew install node');
            } else {
                $this->io->showErr('Node/npm not installed please install Node/npm, and then install bower (OR re-run this command)');
            }
            return;
        }

        if (!$this->checkIsInstalled('bower')) {
            $this->io->writeln('Installing bower:', 'green');
            system('npm install -g bower');
        }

        if (file_exists($this->application()->basePath() . '/bower.json')) {
            $this->io->writeln('Running bower:', 'green');
            system('bower install');
        } else {
            $value = $this->io->ask('Would you like to initialize bower?', 'yes', ['yes', 'no']);
            if ($value === 'yes') {
                system('bower init');
            }
        }

    }

    private function changeFolderPermissions($folder, $mode)
    {
        if (!empty($errors = chmodDirFiles($folder, $mode))) {
            foreach($errors as $error) {
                $this->io->writeln("Unable to change permissions for {$error}", 'red');
            }
        }
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