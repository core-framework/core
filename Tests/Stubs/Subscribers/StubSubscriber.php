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


namespace Core\Tests\Stubs\Subscribers;

use Core\Contracts\Events\Dispatcher;
use Core\Contracts\Events\Subscriber;

class StubSubscriber implements Subscriber
{
    /**
     * @inheritDoc
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->on('Core\Tests\Stubs\Events\StubEvent', '\Core\Tests\Stubs\Subscribers\StubSubscriber@increment', 0);
        $dispatcher->on('Core\Tests\Stubs\Events\StubEvent', '\Core\Tests\Stubs\Subscribers\StubSubscriber@multiply', 2);
        $dispatcher->on('some\subscriber\add', '\Core\Tests\Stubs\Subscribers\StubSubscriber@add', 0);
        $dispatcher->on('some\subscriber\sub', '\Core\Tests\Stubs\Subscribers\StubSubscriber@subtract', 0);
    }

    public function increment($event)
    {
        $event->counter->count++;
    }

    public function subtract($counter, $payload)
    {
        $counter->count -= $payload;
    }

    public function add($counter, $payload)
    {
         $counter->count += $payload;
    }

    public function multiply($event)
    {
        $event->counter->count *= 2;
    }
}