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

namespace Core\Tests\Container;

use Core\Application\Application;
use Core\Config\Config;
use Core\Container\Container;
use Core\Tests\Mocks\MockPaths;
use Core\View\View;
use org\bovigo\vfs\vfsStream;

class ContainerTest extends \PHPUnit_Framework_TestCase {

    public $app;

    public function setUp()
    {
        MockPaths::createMockPaths();
        $this->app = new Application(MockPaths::$basePath);
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::get
     * @throws \ErrorException
     */
    public function testReferenceMatch()
    {
        $Container = new Container();
        $Container->register('_Container', $Container);
        $Container->register('Smarty', '\\Smarty');
        $Container->register('Application', $this->app);
        $Container->register('View', '\\Core\\View\\View')->setArguments(array('Application'));

        /** @var View $a */
        $a = $Container->get('View');
        $a->setShowHeader(true);
        /** @var View $b */
        $b = $Container->get('View');
        $b->setShowFooter(true);
        $c = $Container->get('View');

        $this->assertEquals($a, $c);
        $this->assertEquals($b, $c);
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::get
     * @throws \ErrorException
     */
    public function testCanRegisterClass()
    {
        $Container = new Container();
        $Container->register('Cache', \Core\Cache\ApcCache::class);

        $cache = $Container->get('Cache');

        $this->assertInstanceOf('\\Core\\Cache\\ApcCache', $cache);
    }

} 
