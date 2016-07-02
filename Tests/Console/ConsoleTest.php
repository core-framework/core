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

namespace Core\Tests\Console;

use Core\Console\Console;
use Core\Container\Container;
use Core\Tests\Mocks\MockPaths;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var $cli Console
     */
    public $cli;

    public function setUp()
    {
        MockPaths::createMockPaths();
        $this->cli = new Console(MockPaths::$basePath);
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    /**
     * @covers \Core\Console\CLI::__construct
     */
    public function testCLIConstructor()
    {
        $this->assertInstanceOf('\\Core\\Console\\Console', $this->cli);
        $this->assertInstanceOf('\\Core\\Console\\IOStream', $this->cli->getIO());
    }

    /**
     * @covers \Core\Console\CLI::loadConf
     */
    public function testIfServiceIsSetWhenProvided()
    {
        $this->assertInstanceOf('\\stdClass', $this->cli->get('testService'));
    }

    /**
     * @covers \Core\Console\CLI::loadConf
     */
    public function testIfCommandExistsWhenProvided()
    {
        $this->assertArrayHasKey('hello:world', $this->cli->getCommand());
        $this->assertInstanceOf('\\Core\\Console\\Command', $this->cli->getCommand('hello:world'));
        $this->assertInternalType('callable', $this->cli->getCommand('hello:world')->getDefinition());
    }

    /**
     * @covers \Core\Console\CLI::loadConf
     */
    public function testIfOptionExistsWhenProvided()
    {
        $options = $this->cli->getOptions();
        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('hello:world', $options);
        $this->assertInstanceOf('\\Core\\Console\\Options', $options['hello:world']);
        $this->assertInternalType('callable', $options['hello:world']->getDefinition());
    }

    /**
     * @covers \Core\Console\CLI::setDefaults
     */
    public function testIfDefaultOptionsAreSet()
    {
        $this->assertArrayHasKey('hello:world', $this->cli->getOptions());
    }

}
