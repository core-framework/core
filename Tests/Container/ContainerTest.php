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

    /**
     * @covers \Core\Container\Container::make
     * @covers \Core\Container\Container::get
     */
    public function testMakeMethod()
    {
        $container = new Container();
        $container->make(\Core\Cache\ApcCache::class);
        $cache = $container->get('ApcCache');
        $this->assertInstanceOf('\\Core\\Cache\\ApcCache', $cache);
        $this->assertInstanceOf('\\Core\\Cache\\ApcCache', $container['ApcCache']);
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::findInstance
     * @covers \Core\Container\Container::get
     */
    public function testFindInstanceMethod()
    {
        $Container = new Container();
        $Container->register('_Container', $Container);
        $Container->register('Smarty', '\\Smarty');
        $Container->register('Application', $this->app);
        $Container->register('View', '\\Core\\View\\View')->setArguments(array('Application'));

        $this->assertInstanceOf('\\Core\\View\\View', $Container->findInstance('\\Core\\View\\View'));
        $this->assertInstanceOf('\\Core\\View\\View', $Container->findInstance('View'));
        $this->assertInstanceOf('\\Smarty', $Container->findInstance('Smarty'));
        $this->assertFalse($Container->findInstance('\\some\\unregistered\\class'));
        $fail = 'someReturnValue';
        $this->assertSame($fail, $Container->findInstance('\\some\\unregistered\\class', $fail));
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::serviceExists
     */
    public function testServiceExists()
    {
        $Container = new Container();
        $Container->register('_Container', $Container);
        $Container->register('Smarty', '\\Smarty');
        $Container->register('Application', $this->app);
        $Container->register('View', '\\Core\\View\\View')->setArguments(array('Application'));

        $this->assertTrue($Container->serviceExists('View'));
        $this->assertTrue($Container->serviceExists('Application'));
        $this->assertTrue($Container->serviceExists('Smarty'));
        $this->assertTrue($Container->serviceExists('_Container'));
        $this->assertFalse($Container->serviceExists('unregistered'));
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::get
     * @covers \Core\Container\Container::serviceExists
     * @covers \Core\Container\Container::updateInstance
     */
    public function testUpdateInstance()
    {
        $container = new Container();
        $container->register('stdClass', function (){
            return (object)['id' => 1];
        });

        $this->assertTrue($container->serviceExists('stdClass'));
        $this->assertInstanceOf(\stdClass::class, $container->get('stdClass'));
        $this->assertSame(1, $container->get('stdClass')->id);

        $container->updateInstance('stdClass', (object)['id' => 2]);

        $this->assertTrue($container->serviceExists('stdClass'));
        $this->assertInstanceOf(\stdClass::class, $container->get('stdClass'));
        $this->assertSame(2, $container->get('stdClass')->id);
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::serviceExists
     * @covers \Core\Container\Container::reset
     */
    public function testResetMethod()
    {
        $container = new Container();
        $container->register('stdClass', function (){
            return (object)['id' => 1];
        });
        $this->assertTrue($container->serviceExists('stdClass'));
        $this->assertInstanceOf(\stdClass::class, $container->get('stdClass'));
        $container->reset();
        $this->assertFalse($container->serviceExists('stdClass'));
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::get
     * @covers \Core\Container\Container::setShared
     */
    public function testSetSharedMethod()
    {
        $container = new Container();
        $container->register('stdClass', function (){
            return (object)['id' => 1];
        }, false);
        $this->assertTrue($container->serviceExists('stdClass'));

        $instance1 = $container->get('stdClass');
        $instance2 = $container->get('stdClass');
        $this->assertInstanceOf(\stdClass::class, $instance1);
        $this->assertInstanceOf(\stdClass::class, $instance2);
        $this->assertFalse($instance1 === $instance2);

        $container->setShared('stdClass', true);
        $instance3 = $container->get('stdClass');
        $instance4 = $container->get('stdClass');
        $this->assertInstanceOf(\stdClass::class, $instance3);
        $this->assertTrue($instance3 === $instance4);
    }

    /**
     * @covers \Core\Container\Container::setShared
     * @expectedException \InvalidArgumentException
     */
    public function testSetSharedMethodNonBoolException()
    {
        $container = new Container();
        $container->register('stdClass', function (){
            return (object)['id' => 1];
        }, false);
        $this->assertTrue($container->serviceExists('stdClass'));
        $container->setShared('stdClass', 'someNonBoolValue');
    }

    /**
     * @covers \Core\Container\Container::setShared
     * @expectedException \ErrorException
     */
    public function testSetSharedMethodNoServiceException()
    {
        $container = new Container();
        $container->register('stdClass', function (){
            return (object)['id' => 1];
        }, false);
        $this->assertTrue($container->serviceExists('stdClass'));
        $container->setShared('nonExistentService', true);
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::serviceExists
     * @covers \Core\Container\Container::setDefinition
     */
    public function testSetDefinitionMethod()
    {
        $container = new Container();
        $container->register('stdClass', function (){
            return (object)['id' => 1];
        }, false);

        $this->assertTrue($container->serviceExists('stdClass'));
        $this->assertInstanceOf(\stdClass::class, $container->get('stdClass'));
        $this->assertSame(1, $container->get('stdClass')->id);

        $container->setDefinition('stdClass', function () {
            return (object)['id' => 2];
        });

        $this->assertTrue($container->serviceExists('stdClass'));
        $this->assertInstanceOf(\stdClass::class, $container->get('stdClass'));
        $this->assertSame(2, $container->get('stdClass')->id);
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::getDefinition
     */
    public function testGetDefinitionMethod()
    {
        $container = new Container();
        $container->register('stdClass', function (){
            return (object)['id' => 1];
        }, false);

        $this->assertInstanceOf(\Closure::class, $container->getDefinition('stdClass'));
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::setArguments
     */
    public function testGetArgumentMethod()
    {
        $container = new Container();
        $container->register('stdClass', function ($id) {
            return (object)['id' => $id];
        }, false)->setArguments([10]);

        $this->assertSame(10, $container->get('stdClass')->id);
        $this->assertSame([10], $container->getArguments('stdClass'));
    }



} 
