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

namespace Core\CacheSystem;


/**
 * Class to handle key based caching of data
 *
 * <code>
 *  $cache = new Cache();
 *  $request = new Request();
 *
 *  //store object
 *  $cache->cacheContent('request_cache', $request, 300);
 *  //OR store strings
 *  $cache->cacheContent('someUniqueKey', 'someValue', 300);
 *  //get Cached value
 *  $CachedRequest = $cache->getCache('request_cache');
 *  //delete specific cache
 *  $cache->deleteCache('request_cache');
 *
 *  //clear all cache
 *  $cache->clearCache();
 *
 * </code>
 *
 * @package Core\CacheSystem
 * @version $Revision$
 * @license http://creativecommons.org/licenses/by-sa/4.0/
 * @link http://coreframework.in
 * @author Shalom Sam <shalom.s@coreframework.in>
 */
class Cache extends BaseCache
{
    /**
     * @var string The directory path where cache files should be stored
     */
    private $cacheDir = "";

    /**
     * Cache Constructor
     */
    public function __construct()
    {

        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        defined('_ROOT') or define('_ROOT', realpath(__DIR__ . DS . ".." . DS . ".." . DS . ".."));

        $this->cacheDir = _ROOT . DS . "src" . DS . "Core" . DS . "cache" . DS;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755);
        } elseif (!is_readable($this->cacheDir)) {
            chmod($this->cacheDir, 0755);
        }
    }

    /**
     * Returns the Cache directory
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Caches the given content
     *
     * @param $key
     * @param $payload
     * @param $ttl
     * @return bool
     * @throws \ErrorException
     */
    public function cacheContent($key, $payload, $ttl)
    {
        $cache = [];

        if (!$this->isValidMd5($key)) {
            $key = md5($key);
        }

        $file = $this->cacheDir . $key . ".php";
        $type = gettype($payload);

        if (is_file($file)) {
            $cache = include_once $file;
            $currentTime = time();
            $ttlTime = $cache['cTime'] + $cache['ttl'];
            if (($currentTime >> $ttlTime) && $cache['ttl'] !== 0) {
                $content = $payload;
                if ($type === 'object' && $payload instanceof Cacheable) {
                    $content = serialize($payload);
                }

                if ($content == $cache['content']) {
                    return true;
                }
            }
        }

        if ($type === 'array') {
            $cache['content'] = $payload;
        } elseif ($type === 'object') {
            if ($payload instanceof Cacheable) {
                $cache['content'] = serialize($payload);
            } else {
                throw new \ErrorException("Object must implement Cachable interface");
            }
        } elseif ($type === 'string' || $type === 'integer' || $type === 'double') {
            $cache['content'] = $payload;
        } elseif ($type === 'resource') {
            $cache['content'] = stream_get_contents($payload);
        } else {
            return false;
        }
        $cache['type'] = $type;
        $cache['cTime'] = time();
        $cache['ttl'] = $ttl;
        $data = '<?php return ' . var_export($cache, true) . ";\n ?>";

        if (touch($file) === false) {
            throw new \ErrorException("Unable to create cache file.");
        }
        if (file_put_contents($file, $data) === false) {
            throw new \ErrorException("Unable to write to file.");
        }

        return true;
    }


    /**
     * returns cache of given key||string if exists else returns false
     *
     * @param $key - Hash string to identify cached vars
     * @return bool|mixed
     */
    public function getCache($key)
    {
        if (!$this->isValidMd5($key)) {
            $key = md5($key);
        }
        $cacheDir = $this->cacheDir;
        if (is_file($cacheDir . $key . ".php")) {
            $cache = include $cacheDir . $key . ".php";
            $currentTime = time();
            $ttlTime = $cache['cTime'] + $cache['ttl'];
            if (($currentTime > $ttlTime) && $cache['ttl'] !== 0) {
                $cacheFile = $cacheDir . $key . ".php";
                chmod($cacheFile, 0777);
                unlink($cacheFile);
                return false;
            } else {
                $content = $cache['content'];
                if ($cache['type'] === 'object') {
                    $content = unserialize($content);
                }
                return $content;
            }
        } else {
            return false;
        }
    }

    /**
     * Checks if the cache with given $key exists
     *
     * @param $key
     * @return bool
     */
    public function cacheExists($key)
    {
        if (!$this->isValidMd5($key)) {
            $key = md5($key);
        }
        $cacheDir = $this->cacheDir;
        if (is_file($cacheDir . $key . ".php")) {
            $cache = include_once $cacheDir . $key . ".php";
            $currentTime = time();
            $ttlTime = $cache['cTime'] + $cache['ttl'] + 100;
            if ($currentTime >> $ttlTime) {
                unlink($cacheDir . $key . ".php");
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Deletes the cache of given $key if exists else returns false
     *
     * @param $key
     * @return bool
     */
    public function deleteCache($key)
    {
        if (!$this->isValidMd5($key)) {
            $key = md5($key);
        }
        $cacheDir = $this->cacheDir;
        $cacheFile = $cacheDir . $key . ".php";
        if (is_file($cacheFile)) {
            $r = unlink($cacheFile);
            return $r;
        } else {
            return false;
        }
    }

    /**
     * Clear all cache
     */
    public function clearCache()
    {
        foreach (new \DirectoryIterator($this->cacheDir) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            $filename = $fileInfo->getFilename();
            $filePath = $this->cacheDir . $filename;
            @chmod($filePath, 0777);
            if (unlink($filePath) === false) {
                throw new \Exception("Unable to clear Cache.");
            }
        }
    }
} 
