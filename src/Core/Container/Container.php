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

namespace Core\Container;

use Core\Contracts\Service as ServiceInterface;

/**
 * Class Container
 *
 * <code>
 *  $di = new Container()
 *  $di->bind('View', '\\Core\\View\\View')
 *      ->setArguments(array('Smarty'));
 *  $di->bind('Smarty', '')
 *      ->setDefinition(function() {
 *          return new Smarty();
 *      })
 *
 *  //OR
 *  Container::bind(....)
 *
 * //Later to get services
 *  $view = Container::get('View');
 * //OR
 *  $view = $di->get('View);
 * </code>
 *
 * @package Core\Container
 * @version $Revision$
 * @license http://creativecommons.org/licenses/by-sa/4.0/
 * @link http://coreframework.in
 * @author Shalom Sam <shalom.s@coreframework.in>
 */
class Container implements \ArrayAccess
{
    /**
     * @var Service[] Array of service objects definitions
     */
    protected static $services = [];
    /**
     * @var object[] Array of shared service instances
     */
    protected static $sharedInstances = [];

    /**
     * @param $name
     * @param $class
     * @param null $arguments
     * @param null|bool $singleton
     * @return object
     * @throws \ErrorException
     */
    public static function make($class, $arguments = null, $name = null, $singleton = true)
    {
        if (!is_string($class)) {
            throw new \InvalidArgumentException('$class must be a fully qualified class name');
        }

        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Given class:{$class} does not exist or not found");
        }

        if ($singleton && !is_null($name) && static::$sharedInstances[$name]) {
            return static::$sharedInstances[$name];
        }

        if ($instance = self::findInstance($class)) {
            return $instance;
        }

        return self::makeInstance($class, $arguments, $singleton);
    }

    /**
     * @param string|object $namespacedClass
     * @param bool|mixed $fail
     * @return bool|mixed
     */
    public static function findInstance($namespacedClass, $fail = false)
    {
        try {

            if (!strContains('\\', $namespacedClass) && static::serviceExists($namespacedClass)) {
                return self::get($namespacedClass);
            } elseif (!strContains('\\', $namespacedClass) && !static::serviceExists($namespacedClass)) {
                return $fail;
            }

            $reflection = new \ReflectionClass($namespacedClass);
            $class = $reflection->getShortName();
            $namespacedClass = $reflection->getName();

            // search by class name
            if (self::serviceExists($class)) {
                $object = self::get($class);
                if ($object instanceof $namespacedClass) {
                    return $object;
                }
            }

            // search shared instances
            foreach (static::$sharedInstances as $name => $instance) {
                if ($instance instanceof $namespacedClass) {
                    return $instance;
                }
            }

            // search services
            foreach (static::$services as $name => $service) {
                $instance = self::get($name);
                if ($instance instanceof $namespacedClass) {
                    return $instance;
                }
            }

        } catch (\Exception $e) {
            return $fail;
        }

        return $fail;
    }

    /**
     * Return true if given service exists, else false
     *
     * @param $name
     * @return bool
     */
    public static function serviceExists($name)
    {
        return isset(self::$services[$name]) || isset(self::$sharedInstances[$name]);
    }

    /**
     * Lazy load the given service object
     *
     * @param $name
     * @return object
     * @throws \ErrorException
     */
    public static function get($name)
    {
        if (empty($name)) {
            throw new \ErrorException("Service name cannot be empty");
        }

        if (!is_string($name)) {
            throw new \ErrorException("Service name must be a valid string");
        }

        if (!self::serviceExists($name)) {
            throw new \ErrorException(
                "Service of type {$name} not found. Service {$name} must be registered before use."
            );
        }

        //self::$services[$name]->getShared() === true
        if (empty(self::$sharedInstances[$name]) === false) {
            return self::$sharedInstances[$name];
        }

        $definition = self::$services[$name]->getDefinition();
        $arguments = self::$services[$name]->getArguments();
        $shared = self::$services[$name]->getShared();

        if ($definition instanceof \Closure) {

            $reflection = new \ReflectionFunction($definition);

            if (empty($arguments)) {
                $action = $reflection->invoke();
            } else {
                $action = $reflection->invokeArgs($arguments);
            }

            if ($shared) {
                self::$sharedInstances[$name] = $action;
            }

            return $action;

        } elseif (is_object($definition)) {

            if ($shared) {
                self::$sharedInstances[$name] = $definition;
                return self::$sharedInstances[$name];
            }
            return $definition;

        } elseif (is_string($definition) && class_exists($definition)) {

            $r = new \ReflectionClass($definition);

            if (is_null($arguments)) {

                if ($shared) {
                    self::$sharedInstances[$name] = $r->newInstance();
                    return self::$sharedInstances[$name];
                }

                return $r->newInstance();

            } else {

                $arguments = self::resolveDependencies($arguments);

                if ($shared) {
                    self::$sharedInstances[$name] = $r->newInstanceArgs($arguments);
                    return self::$sharedInstances[$name];
                }

                return $r->newInstanceArgs($arguments);

            }
        } else {
            throw new \ErrorException(
                "Definition must either be a namespaced class or a Closure returning an object or a namespaced class."
            );
        }
    }

    /**
     * Checks and returns dependencies passed as argument
     *
     * @param $arguments
     * @return array
     * @throws \ErrorException
     */
    protected static function resolveDependencies($arguments)
    {
        if (!is_array($arguments)) {
            throw new \ErrorException("Argument(s) must be an Array of arguments.");
        }

        if (empty($arguments)) {
            return [];
        }

        $return = [];

        foreach ($arguments as $key => $val) {
            if (is_string($val) && strpos($val, '::') !== false) {
                $return[] = call_user_func($val);
            } elseif (is_string($val) && (class_exists($val) || self::serviceExists($val))) {
                $return[] = self::get($val);
            } else {
                $return[] = $val;
            }
        }

        return $return;

    }

    /**
     * @param $class
     * @param $args
     * @param bool $singleton
     * @return object
     */
    protected static function makeInstance($class, $args = null, $singleton = true)
    {
        if (is_null($args)) {
            $args = [];
        }

        $reflection = new \ReflectionClass($class);
        $name = $reflection->getShortName();
        $constructor = $reflection->getConstructor();

        if ($constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                $args[] = self::findInstance($parameter);
            }
        }

        if (empty($args)) {
            $instance = $reflection->newInstance();
        } else {
            $instance = $reflection->newInstanceArgs($args);
        }

        if ($singleton) {
            self::$sharedInstances[$name] = $instance;
        }

        return $instance;
    }

    /**
     * @param $name
     * @param $instance
     */
    public static function updateInstance($name, $instance)
    {
        static::$sharedInstances[$name] = $instance;
    }

    /**
     * Reset Container
     */
    public static function reset()
    {
        static::$services = [];
        static::$sharedInstances = [];
    }

    /**
     * Sets given service as shared
     *
     * @param $name
     * @param $shared
     * @return ServiceInterface
     * @throws \ErrorException
     */
    public function setShared($name, $shared = true)
    {
        if (!is_bool($shared)) {
            throw new \InvalidArgumentException(
                "setShared method's second argument must be a boolean value. {$shared} (" . gettype(
                    $shared
                ) . ") given."
            );
        }
        if (!self::$services[$name]) {
            throw new \ErrorException("Service must be registered first.");
        }


        self::$services[$name]->setShared($shared);

        return self::$services[$name];
    }

    /**
     * Set service implementation definition
     *
     * @param $name
     * @param $definition
     * @return ServiceInterface
     * @throws \ErrorException
     */
    public function setDefinition($name, $definition)
    {
        if (!self::$services[$name]) {
            throw new \ErrorException("Service must be registered first.");
        }

        self::$services[$name]->setDefinition($definition);

        return self::$services[$name];
    }

    /**
     * Returns the Definition for given service name
     *
     * @param $name
     * @return mixed
     * @throws \ErrorException
     */
    public function getDefinition($name)
    {
        if (!self::$services[$name]) {
            throw new \ErrorException("Service must be registered first.");
        }

        return self::$services[$name]->getDefinition();
    }

    /**
     * Returns the set Arguments for the given service name
     *
     * @param $name
     * @return mixed
     * @throws \ErrorException
     */
    public function getArguments($name)
    {
        if (!self::$services[$name]) {
            throw new \ErrorException("Service must be registered first.");
        }

        return self::$services[$name]->getArguments();
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset(self::$services[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return self::get($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        self::register($offset, $value);
    }

    /**
     * @param $name
     * @param $definition
     * @param bool $singleton
     * @return Service
     * @throws \ErrorException
     */
    public static function register($name, $definition, $singleton = true)
    {
        if (!is_string($name)) {
            throw new \ErrorException("Service name must be a valid string.");
        }

        if (!is_bool($singleton)) {
            throw new \ErrorException("Incorrect parameter type.");
        }

        self::$services[$name] = new Service($name, $definition, $singleton);

        if (isset(self::$sharedInstances[$name])) {
            unset(self::$sharedInstances[$name]);
        }

        return self::$services[$name];
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset(self::$services[$offset]);
    }


    /**
     * Magic sleep method for serialization
     *
     * @return array
     */
    public function __sleep()
    {
        return ['sharedInstances'];
    }

}

