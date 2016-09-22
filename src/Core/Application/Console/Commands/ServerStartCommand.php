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

class ServerStartCommand extends Command
{
    public function init()
    {
        $this->setName('server:start');
        $this->setDescription('Create and run a development server for current application');
        $this->addOption('host', 'h', 'The Host (IP) address');
        $this->addOption('port', 'p', 'The Port number');
        $this->addOption('docroot', 'd', 'The document root directory path');
        $this->addOption('router', 'r', 'The Router file path');
    }


    public function execute(IOStream $io)
    {
        $docRoot = $this->application()->publicFolder();
        $docRoot = $this->options('docroot', $docRoot);
        $router = $this->options('router', "{$docRoot}/index.php");
        $host = $this->options('host', 'localhost');
        $port = $this->options('port', '8000');
        $binary = PHP_BINARY;
        $base = $this->application()->basePath();

        chdir($docRoot);

        $io->writeln("Core Framework Development Server started at http://{$host}:{$port}/");

        if (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.8.0') >= 0) {
            passthru("{$binary} -m server -v Server.Type=proxygen -v Server.SourceRoot={$base}/ -v Server.IP={$host} -v Server.Port={$port} -v Server.DefaultDocument={$router} -v Server.ErrorDocument404={$router}");
        } else {
            passthru("{$binary} -S {$host}:{$port} {$router}");
        }
    }
}