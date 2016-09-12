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

namespace Core\Contracts;


use Core\Reactor\DataCollection;

interface Config
{
    /**
     * Checks if Key exists in collection
     * 
     * @param $key
     * @return bool
     */
    public function has($key);
    
    /**
     * Find Key value in collection, returns default on failure
     *
     * @param null $key Key to be searched
     * @param bool $default Default value to return on failure
     * @return array|bool|mixed
     */
    public function get($key = null, $default = false);

    /**
     * @return array|bool|mixed|null
     */
    public function getDatabase();

    /**
     * @param string $type
     * @return array
     */
    public function getConnection($type = 'mysql');

    /**
     * @return array|bool|mixed|null
     */
    public function all();
    

    /**
     * @return array|bool|mixed|null
     */
    public function getServices();

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value);
    
    /**
     * Calls callback on each item in given Array and makes a collection of all return values
     *
     * @param array $array Array to be looped
     * @param \Closure $callback Closure to be called each Key/Value pair of given Array
     * @return DataCollection
     */
    public static function each(array $array, \Closure $callback);
    
    
    /**
     * Finds Key/Value pair in given Array that passes a truth test, and returns Value of matched pair.
     *
     * @param array $array Array to be looped
     * @param \Closure $callback Truth test callback
     * @param bool $default Default value to return if all else fails
     * @return bool|mixed
     */
    public static function find(array $array, \Closure $callback, $default = false);
    
    
    /**
     * Finds Key/Value pair in Collection that passes a truth test, and returns Value of matched pair.
     *
     * @param \Closure $callback Truth test callback
     * @param bool $default Default value to return if all else fails
     * @return bool|mixed
     */
    public function map(\Closure $callback, $default = false);
    
    
    
}