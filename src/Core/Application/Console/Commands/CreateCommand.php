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
use Core\Contracts\FileSystem\FileSystem;
use Core\Database\Migration\Migration;

class CreateCommand extends Command
{
    public function init()
    {
        $this->setName('create');
        $this->setDescription('Command to create new class files');
        $this->addArgument('fileType', 'The type of class to create', null, true)->mustValidate(new OptionsValidator(
            [
                'command' => 'Creates a new Core Framework Command class file',
                'controller' => 'Creates a new Core Framework Controller class file',
                'event' => 'Creates a new Core Framework Event class file',
                'listener' => 'Creates a new Core Framework EventListener class file',
                'middleware' => 'Creates a new Core Framework Middleware class file',
                'model' => 'Creates a new Core Framework Model class file',
                'migration' => 'Creates a new Core Framework Database Migration file',
                'bootstrapper' => 'Create a new Core Framework Bootstrap file',
            ]
        ));
        $this->addArgument('name', 'Name for the fileType specified', null, true);
    }

    public function execute(IOStream $io)
    {
        $fileType = $this->input('fileType');
        $name = $this->input('name');

        if (!$fileType || !$name) {
            $io->showErr("Please specify the 'fileType' and 'name' for the given file. See below help for details");
            $this->showHelp($io);
            return;
        }

        $fileSystem = $this->application()->getFileSystem();

        $real = $this->getRealFolder($this->application()->appPath(), $fileType, $io);
        $appFolder = $real['folder'];
        $namespace = $real['namespace'];
        $name = $this->formatName($name, $fileType);


        $replace = [
            '{{$namespace}}' => $namespace,
            '{{$fileName}}' => $name,
            '{{$className}}' => $name
        ];

        $stub = $fileSystem->getContents(__DIR__ . "/../Stubs/{$fileType}.stub");
        $content = str_replace(array_keys($replace), array_values($replace), $stub);
        $filePath = rtrim($appFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.php';

        if ($fileType === 'migration') {
            $migration = new Migration(['migration' => $name, 'batch' => 0]);
            $migration->save();
        }

        if ($fileSystem->write($filePath, $content)) {
            $io->writeln($name . ' file created successfully', 'green');
        } else {
            throw new \RuntimeException('Unable to create file ' . $filePath);
        }

    }

    private function formatName($name, $fileType)
    {
        if (strContains($fileType, $name)) {
            $name = str_replace($fileType, '', strtolower($name));
        }

        $name = $name . ucfirst($fileType);
        return $name;
    }

    private function getRealFolder($appFolder, $fileType, IOStream $io)
    {
        $return = [
            'folder' => $appFolder . DIRECTORY_SEPARATOR . ucfirst($fileType) . 's',
            'namespace' => "App\\" . ucfirst($fileType) . 's'
        ];
        if (!is_dir($appFolder)) {
            $return['folder'] = $io->ask("App folder missing! Please Specify the Folder to create the file in:");
            if (!is_dir($appFolder)) {
                throw new \InvalidArgumentException("Directory {$appFolder} doesn't exist.");
            }
            $return['namespace'] = $io->ask("Please provide namespace for given folder");
        }
        return $return;
    }

}