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

namespace Core\Reactor;

class DataCollection implements \IteratorAggregate, \Countable, \ArrayAccess, \Serializable
{
    protected $collection;
    protected $_cache;

    /**
     * Parameters constructor.
     * @param $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->collection = $parameters;
    }

    /**
     * Calls callback on each item in given Array and makes a collection of all return values
     *
     * @param array $array Array to be looped
     * @param \Closure $callback Closure to be called each Key/Value pair of given Array
     * @return DataCollection
     */
    public static function each(array $array, \Closure $callback)
    {
        $collection = [];
        foreach ($array as $key => $val) {
            $collection[] = call_user_func($callback, $key, $val);
        }

        return new static($collection);
    }

    /**
     * Finds Key/Value pair in given Array that passes a truth test, and returns Value of matched pair.
     *
     * @param array $array Array to be looped
     * @param \Closure $callback Truth test callback
     * @param bool $default Default value to return if all else fails
     * @return bool|mixed
     */
    public static function find(array $array, \Closure $callback, $default = false)
    {
        foreach ($array as $key => $val) {
            if (call_user_func($callback, $key, $val) === true) {
                return $val;
            }
        }

        return $default;
    }

    /**
     * Find Key value in collection, returns default on failure
     *
     * @param null $key Key to be searched
     * @param bool $default Default value to return on failure
     * @return array|bool|mixed
     */
    public function get($key = null, $default = false)
    {
        $hash = md5($key);
        // internal cache check
        if (isset($this->_cache[$hash])) {
            return $this->_cache[$hash];
        }

        if (is_null($key)) {
            $this->_cache[$hash] = $this->collection;
            return $this->collection;
        } elseif (strpos($key, '.') !== false) {
            $response = dotGet($key, $this->collection, $default);
            $this->_cache[$hash] = $response;
            return $response;
        } elseif (array_key_exists($key, $this->collection)) {
            $this->_cache[$hash] = $this->collection[$key];
            return $this->collection[$key];
        } else {
            $response = searchArrayByKey($this->collection, $key, $default);
            $this->_cache[$hash] = $response;
            return $response;
        }
    }

    /**
     * Checks if Key exists in collection
     *
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        if (strpos($key, '.') !== false) {
            return dotGet($key, $this->collection, false) ? true : false;
        } else {
            return $this->offsetExists($key);
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    /**
     * Add Key/Value pair to collection
     *
     * @param $key string
     * @param $value mixed
     */
    public function set($key, $value)
    {
        if (strpos($key, '.') !== false) {
            dotSet($key, $this->collection, $value);
        } elseif (is_null($key)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$key] = $value;
        }
    }

    /**
     * Finds Key/Value pair in Collection that passes a truth test, and returns Value of matched pair.
     *
     * @param \Closure $callback Truth test callback
     * @param bool $default Default value to return if all else fails
     * @return bool|mixed
     */
    public function map(\Closure $callback, $default = false)
    {
        $array = $this->collection;
        foreach ($array as $key => $val)
        {
            if (call_user_func($callback, $key, $val) === true) {
                return $val;
            }
        }

        return $default;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize($this->collection);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        return unserialize($this->collection);
    }


}