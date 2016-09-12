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
use Core\Database\Mapper\MySqlMapper;
use Core\Database\Table;

class MigrateInstallCommand extends Command
{
    public function init()
    {
        $this->setName('migrate:install');
        $this->setDescription('Install/Setup migration table in database');
        $this->addOption('environment', 'e', 'Set Application environment');
    }

    public function execute(IOStream $io)
    {
        if ($this->options('environment')) {
            $this->application()->setEnvironment($this->options('environment'));
        }
        $mapper = $this->application()->getMapper();
        if ($mapper->hasTable('migration_log')) {
            $mapper->dropTable('migration_log');
        }
        $table = new Table('migration_log', [], $mapper);
        $res = $table->addColumn('id', 'integer', ['null' => false, 'primaryKey' => true, 'autoIncrement' => true])
            ->addColumn('migration', 'string')
            ->addColumn('batch', 'integer')
            ->addTimestamps()
            ->create();

        if ($res) {
            $io->writeln('Migration table setup successfully', 'green');
        } else {
            $io->showErr('Unable to create migration table in database');
        }
    }
}