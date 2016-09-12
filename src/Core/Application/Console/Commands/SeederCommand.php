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
use Core\Application\Console\Option;
use Core\Contracts\Application\Console\IOStream;
use Core\Database\Seeder\AbstractSeeder;
use Core\FileSystem\Explorer;

class SeederCommand extends Command
{
    /**
     * @var string
     */
    public $namespace = 'app\\Seeders';
    /**
     * @var IOStream $io
     */
    public $io;

    public function init()
    {
        $this->setName('seeder:run');
        $this->setDescription("Add records into database tables");
        $this->addOption('environment', 'e', 'To set the target environment');
        $this->addOption('class', 'c', 'The class name of the Seeder file to execute', Option::OPTION_REQUIRED);

    }

    public function execute(IOStream $io)
    {
        $folder = $this->application()->appPath() . DIRECTORY_SEPARATOR . 'Seeders';
        $this->io = $io;
        if ($env = $this->options('environment')) {
            $this->application()->setEnvironment($env);
        }

        if ($class = $this->options('class')) {
            if (strContains(',', $class)) {
                $classes = array_map('trim', explode(',', $class));
                foreach($classes as $class) {
                    $this->runClass($class);
                }
            } else {
                $this->runClass($class);
            }
        } else {

            $this->runAllInFolder($folder);
        }
    }

    private function runClass($class)
    {
        if (!strContains('\\', $class)) {
            $namespacedClass = $this->namespace . '\\' . $class;
        } else {
            $namespacedClass = $class;
        }

        /** @var AbstractSeeder $seeder */
        $seeder = new $namespacedClass($this->application()->getMapper());
        $seeder->run();

        $this->io->writeln("Seeding of {$class} completed Successfully", 'green');

    }

    private function runAllInFolder($folder)
    {
        Explorer::find()->files("*.php")->in($folder)->map(function ($key, $fileInfo) {
           /** @var \SplFileInfo $fileInfo */
           if ($fileName = $fileInfo->getFilename()) {
               $className = str_replace('.php', '', $fileName);
               $this->runClass($className);
           }
        });
    }
}