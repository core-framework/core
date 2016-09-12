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
use Core\Application\Console\Validators\OptionsValidator;
use Core\Contracts\Application\Console\IOStream;
use Core\Database\Migration\AbstractMigration;
use Core\Database\Migration\Migration;
use Core\FileSystem\Explorer;
use Core\FileSystem\Tokenizer;

class MigrateRunCommand extends Command
{
    /**
     * @var string
     */
    public $namespace = 'app\\Migrations';
    /**
     * @var string
     */
    public $direction = 'up';
    /**
     * @var IOStream $io
     */
    public $io;

    public function init()
    {
        $this->setName('migrate:run');
        $this->setDescription('Run database migrations');
        $this->addArgument('direction', 'The migration Direction', 'up', true)->addValidation(new OptionsValidator([
            'up' => 'Runs migration up command',
            'down' => 'Runs migration down command'
        ]));
        $this->addOption('environment', 'e', 'To set the target environment');
        $this->addOption('class', 'c', 'The migration class name of the file on which migrations are to be run');
        $this->addOption('folder', 'o', 'The folder containing the migration files');
        $this->addOption('date', 'd', 'The date to migrate to');
    }

    public function execute(IOStream $io)
    {
        $this->io = $io;
        $this->direction = $this->input('direction');
        $env = $this->options('environment');
        $class = $this->options('class');
        $folder = $this->options('folder');
        $date = $this->options('date');


        if ($env) {
            $this->application()->setEnvironment($env);
        }

        if ($class && $folder) {
            $this->runClassAndFolder($class, $folder);
        } elseif (!$class && $folder) {
            $this->runAllInFolder($folder);
        } elseif ($class && !$folder) {
            $this->runClass($class);
        } elseif ($date) {
            $this->runDate($date);
        } else {
            $this->runDefault();
        }

    }

    public function runClassAndFolder($class, $folder)
    {
        $namespace = $this->namespace;
        $fileName = $class . '.php';

        if (!strContains('\\', $class)) {
            $namespacedClass = $namespace . '\\' . $class;
        } else {
            $namespacedClass = $class;
            $parts = explode('\\', $class);
            $class = $parts[sizeof($parts) - 1];
        }

        $filePath = $folder . $fileName;
        require_once $filePath;

        if ($this->migrateRun($namespacedClass, $class)) {
            $this->io->writeln("Migrated {$this->direction} file {$filePath} successfully!", 'green');
        } else {
            $this->io->showErr("Migration of {$filePath} failed!");
        }

        if ($this->options('verbose')) {
            $this->io->writeln("Status:", 'green');
            $this->showStatus();
        }
    }

    public function runAllInFolder($folder)
    {
        Explorer::find()->files('*.php')->in($folder)->map(function ($key, $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            if ($path = $fileInfo->getPathname()) {
                require_once $path;

                $tokenizer = new Tokenizer($path);

                if ($this->migrateRun($tokenizer->getFullyQualifiedClass(), $tokenizer->getClass())) {
                    $this->io->writeln("Migration {$this->direction} in class {$tokenizer->getFullyQualifiedClass()} ran successfully!", 'green');
                } else {
                    $this->io->showErr("Migration {$this->direction} in class {$tokenizer->getFullyQualifiedClass()} failed!");
                }
            }
        });

        if ($this->options('verbose')) {
            $this->io->writeln("Status:", 'green');
            $this->showStatus();
        }
    }

    public function runClass($class)
    {
        if (!strContains('\\', $class)) {
            $namespacedClass = $this->namespace . '\\' . $class;
        } else {
            $namespacedClass = $class;
            $parts = explode('\\', $class);
            $class = $parts[sizeof($parts) - 1];
        }

        if ($this->migrateRun($namespacedClass, $class)) {
            $this->io->writeln("Migration {$this->direction} in class {$namespacedClass} ran successfully!", 'green');
        } else {
            $this->io->showErr("Migration {$this->direction} in class {$namespacedClass} failed!");
        }

        if ($this->options('verbose')) {
            $this->io->writeln("Status:", 'green');
            $this->showStatus();
        }
    }

    public function runDate($date)
    {
        $this->prepare();

        $migrations = Migration::where('modified_at', $date);
        foreach ($migrations as $i => $migrationModel) {
            $this->runClass($migrationModel->migration);
        }
    }

    public function runDefault()
    {
        $this->prepare();

        $migrations = Migration::find();
        foreach ($migrations as $i => $migrationModel) {
            $this->runClass($migrationModel->migration);
        }
    }

    private function prepare()
    {
        $defaultMigrationFolder = $this->application()->appPath() . DIRECTORY_SEPARATOR . 'Migrations' . DIRECTORY_SEPARATOR;
        $migrationFolder = $this->options('folder', $defaultMigrationFolder);
        Explorer::find()->files('*.php')->in($migrationFolder)->map(function ($key, $fileInfo) use ($migrationFolder, $defaultMigrationFolder) {
            /** @var \SplFileInfo $fileInfo */
            if ($path = $fileInfo->getPath()) {

                if ($migrationFolder !== $defaultMigrationFolder) {
                    require_once $path;
                    $tokenizer = new Tokenizer($path);
                    $class = $tokenizer->getFullyQualifiedClass();
                } else {
                    $class = str_replace('.php', '', $fileInfo->getFilename());
                }

                $migrationModel = Migration::findOne(['migration' => $class]);

                if (!$migrationModel) {
                    $migrationModel = new Migration(['migration' => $class, 'batch' => 0]);
                    $migrationModel->save();
                }
            }
        });
    }

    private function migrateRun($namespacedClass, $class)
    {
        $save = false;
        /** @var AbstractMigration $migrationClass */
        $migrationClass = new $namespacedClass($this->application()->getMapper());
        $migrationClass->{$this->direction}();

        if (strContains($this->namespace, $namespacedClass)) {
            $migrationName = $class;
        } else {
            $migrationName = $namespacedClass;
        }

        $migrationModel = Migration::findOne(['migration' => $migrationName]);

        if (!$migrationModel) {
            $migrationModel = new Migration(['migration' => $migrationName, 'batch' => 0]);
            $save = true;
        }

        if ($this->direction === 'up') {
            $migrationModel->batch++;
        } elseif ($this->direction === 'down' && $migrationModel->count !== 0) {
            $migrationModel->batch--;
        }

        if ($save) {
            return $migrationModel->save();
        } else {
            return $migrationModel->update();
        }

    }

    private function showStatus()
    {
        /** @var Migration[] $all */
        $all = Migration::find();
        foreach ($all as $i => $model) {
            $all[$i] = $model->toArray();
        }
        $headers = ['id', 'migration', 'batch', 'created_at', 'modified_at'];
        array_unshift($all, $headers);
        $this->io->showTable($all);
    }
}