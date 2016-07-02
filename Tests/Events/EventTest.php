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


namespace Core\Tests\Events;

use Core\Container\Container;
use Core\Facades\Event;
use Core\Tests\Stubs\Events\StubEvent;

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $container = new Container();
        $event = $container->register('Event', \Core\Events\Dispatcher::class);
        $event->setArguments([$container]);
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidListenersThrowsError()
    {
        Event::on('some.core.event', ['someClass'], 1);
        Event::on('some.core.event', false, 1);
    }

    /**
     * @covers \Core\Events\Dispatcher::getContainer
     */
    public function testGetContainer()
    {
        $this->assertInstanceOf('\Core\Container\Container', Event::getContainer());
    }

    /**
     * @covers \Core\Events\Dispatcher::hasListener
     */
    public function testHasListener()
    {
        Event::on('test.hasListener', function() {
            return 'test';
        });

        $this->assertTrue(Event::hasListener('test.hasListener'));
    }

    /**
     * @covers \Core\Events\Dispatcher::on
     * @covers \Core\Events\Dispatcher::addListener
     * @covers \Core\Events\Dispatcher::makeFromObjectListener
     * @covers \Core\Events\Dispatcher::makeClosure
     * @covers \Core\Events\Dispatcher::dispatch
     * @covers \Core\Events\Dispatcher::getListener
     */
    public function testHandleEventObject()
    {
        Event::on('Core\Tests\Stubs\Events\StubEvent', function($event) {
            $counter = $event->counter;
            $counter->count++;
        });

        $counter = new \stdClass();
        $counter->count = 0;

        $stubEvent = new StubEvent($counter);
        Event::dispatch($stubEvent);

        $this->assertEquals(1, $counter->count);
    }

    /**
     * @covers \Core\Events\Dispatcher::on
     * @covers \Core\Events\Dispatcher::addListener
     * @covers \Core\Events\Dispatcher::makeFromObjectListener
     * @covers \Core\Events\Dispatcher::makeClosure
     * @covers \Core\Events\Dispatcher::dispatch
     * @covers \Core\Events\Dispatcher::getListener
     */
    public function testHandleEventString()
    {
        Event::on('Core.router.matched', function($route) {
            return $route->path;
        });

        $router = new \stdClass();
        $router->path = '/some/path';

        $response = Event::dispatch('Core.router.matched', $router);
        $this->assertInternalType('array', $response);
        $this->assertEquals($response[0], $router->path);
    }

    /**
     * @covers \Core\Events\Dispatcher::on
     * @covers \Core\Events\Dispatcher::addListener
     * @covers \Core\Events\Dispatcher::makeFromStringListener
     * @covers \Core\Events\Dispatcher::makeClosure
     * @covers \Core\Events\Dispatcher::dispatch
     * @covers \Core\Events\Dispatcher::getListener
     */
    public function testWithStubListeners()
    {
        $counter = new \stdClass();
        $counter->count = 2;
        $stubEvent = new StubEvent($counter);

        Event::on('Core\Tests\Stubs\Events\StubEvent', \Core\Tests\Stubs\Listeners\StubListener::class);
        Event::dispatch($stubEvent);
        $this->assertEquals(3, $counter->count);

        Event::on('Core\Tests\Stubs\Events\StubEvent', '\Core\Tests\Stubs\Listeners\StubListenerMethod@someMethod');
        Event::dispatch($stubEvent);
        $this->assertEquals(4, $counter->count);
    }

    /**
     * @covers \Core\Events\Dispatcher::subscribe
     * @covers \Core\Events\Dispatcher::makeSubscriber
     * @covers \Core\Events\Dispatcher::dispatch
     * @covers \Core\Events\Dispatcher::getListener
     */
    public function testEventSubscriber()
    {
        $counter = new \stdClass();
        $counter->count = 2;
        $stubEvent = new StubEvent($counter);
        Event::subscribe('\Core\Tests\Stubs\Subscribers\StubSubscriber');
        Event::dispatch($stubEvent);
        $this->assertEquals(6, $counter->count);

        Event::dispatch('some\subscriber\add', [$counter, 2]);
        $this->assertEquals(8, $counter->count);
        
        Event::dispatch('some\subscriber\sub', [$counter, 5]);
        $this->assertEquals(3, $counter->count);
    }
}
