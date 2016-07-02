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


namespace Core\Events;

use Core\Container\Container;
use Core\Contracts\Events\Dispatcher as DispatcherInterface;
use Core\Contracts\Events\Subscriber;

class Dispatcher implements DispatcherInterface
{
    protected $listeners = [];
    protected $sorted = [];
    protected $container;

    /**
     * DispatcherKernel constructor.
     * @param Container|null $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container ?: new Container();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @inheritdoc
     */
    public function addListener($name, $listener, $priority = 0)
    {
        $type = strtolower(gettype($listener));
        $method = 'makeFrom' . ucfirst($type) . 'Listener';
        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Invalid Listener({$listener}) of type {$type} given.");
        }

        $this->listeners[$name][$priority][] = $this->{$method}($listener);
    }

    /**
     * @inheritdoc
     */
    public function on($name, $listener, $priority = 0)
    {
        $this->addListener($name, $listener, $priority);
    }

    /**
     * @inheritdoc
     */
    public function hasListener($name)
    {
        return isset($this->listeners[$name]);
    }

    /**
     * @param $listener
     * @return \Closure
     */
    protected function makeFromObjectListener($listener)
    {
        if (is_callable($listener) || $listener instanceof \Closure) {
            return $listener;
        }
        return $this->makeClosure($listener, 'handle');
    }

    /**
     * @param $listener
     * @return \Closure
     */
    protected function makeFromStringListener($listener)
    {
        if (strContains('@', $listener)) {
            list($class, $method) = explode('@', $listener);
            $obj = $this->container->make($class);
        } else {
            list($obj, $method) = [$this->container->make($listener), 'handle'];
        }

        return $this->makeClosure($obj, $method);
    }

    /**
     * @param array $listener
     * @return \Closure
     */
    protected function makeFromArrayListener(array $listener)
    {
        return $this->makeClosure($listener[0], $listener[1]);
    }

    /**
     * @param $object
     * @param $method
     * @return \Closure
     */
    protected function makeClosure($object, $method)
    {
        return function () use ($object, $method) {
            return call_user_func_array([$object, $method], func_get_args());
        };
    }

    /**
     * @param $name
     * @return array
     */
    public function getListener($name)
    {
        if (!isset($this->listeners[$name])) {
            return [];
        }

        if (!isset($this->sorted[$name])) {
            ksort($this->listeners[$name]);
            $this->sorted[$name] = call_user_func_array('array_merge', $this->listeners[$name]);
        }

        return $this->sorted[$name];
    }

    /**
     * @inheritdoc
     */
    public function subscribe($subscriber)
    {
        $subscriber = $this->makeSubscriber($subscriber);
        $subscriber->subscribe($this);
    }

    /**
     * @param $subscriber
     * @return Subscriber
     * @throws \ErrorException
     */
    protected function makeSubscriber($subscriber)
    {
        if (is_string($subscriber) && !strContains('\\', $subscriber)) {
            return $this->container->get($subscriber);
        } elseif (is_string($subscriber) && strContains('\\', $subscriber)) {
            return $this->container->make($subscriber);
        } elseif (is_object($subscriber) && $subscriber instanceOf Subscriber) {
            return $subscriber;
        } else {
            throw new \InvalidArgumentException("Subscriber must be a valid Service name or Class name implementing Subscribe Interface");
        }
    }

    /**
     * @inheritdoc
     */
    public function dispatch($event, $payload = [], $breakOnFalse = true)
    {
        if (is_object($event)) {
            list($event, $payload) = [get_class($event), [$event]];
        }

        if (!is_array($payload)) {
            $payload = [$payload];
        }

        $responses = [];

        foreach ($this->getListener($event) as $listener) {
            $response = call_user_func_array($listener, $payload);
            
            if ($response === false && $breakOnFalse === true) {
                break;
            }

            $responses[] = $response;
        }

        return $responses;
    }
}