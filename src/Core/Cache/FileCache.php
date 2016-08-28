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


namespace Core\Cache;


use Core\Contracts\Cache as CacheInterface;
use Core\Contracts\Cacheable;
use Core\FileSystem\FileSystem;

class FileCache implements CacheInterface
{
    protected $cacheDir;
    protected $fileSystem;

    /**
     * @inheritDoc
     */
    public function __construct(FileSystem $fileSystem, $cacheDir)
    {
        $this->fileSystem = $fileSystem;
        $this->cacheDir = rtrim($cacheDir, '/');
    }

    /**
     * Checks if given string is a valid MD5 string
     *
     * @param $key
     * @return mixed
     */
    public function isMd5($key)
    {
        return preg_match('/^[a-f0-9_]{32}$/', $key);
    }
    
    /**
     * @inheritDoc
     */
    public function put($key, $payload, $ttl = 0)
    {
        $path = $this->path($key);

        if (!is_file($path)) {
            touch($path);
        }

        $cache = $this->buildCacheArr($payload, $ttl);
        return $this->fileSystem->write($path, serialize($cache));
    }

    /**
     * Build the cache file contents as an array (for serialization)
     *
     * @param mixed $payload
     * @param int $ttl
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function buildCacheArr($payload, $ttl)
    {
        $cache = [];
        if (is_object($payload) && (!$payload instanceof \Serializable && !$payload instanceof Cacheable && !$payload instanceof \stdClass)) {
            throw new \InvalidArgumentException("Object payloads for caching must implement Serializable or Cacheable");
        } elseif (is_resource($payload)) {
            $cache['content'] = stream_get_contents($payload);
        } else {
            $cache['content'] = $payload;
        }

        $cache['type'] = gettype($payload);
        $cache['cTime'] = time();
        $cache['expires'] = time() + $ttl;
        $cache['ttl'] = $ttl;

        return $cache;
    }

    /**
     * Generates and returns file path from given key
     *
     * @param $key
     * @return string
     */
    protected function path($key)
    {
        return $this->cacheDir . '/' . md5($key) . '.php';
    }

    /**
     * @inheritDoc
     */
    public function get($key)
    {
        $path = $this->path($key);

        /* If file doesn't exist false will be returned */
        try {
            $cache = unserialize($this->fileSystem->getContents($path));

            /* if expired and not forever cache, delete cache and return false */
            if (time() >= $cache['expires'] && $cache['ttl'] !== 0) {
                $this->delete($key);
                return false;
            }

            return $cache['content'];

        } catch (\Exception $e) {
            return false;
        }
        
    }

    /**
     * Checks if given cache (array or key) has expired. It is advised to use getContent = true instead of calling the get() method after this check
     *
     * @param string|array $key Cache Key or Cache array
     * @param bool $getContent Whether to return contents of cache if not expired instead of (default) boolean true
     * @return bool|mixed|null
     */
    public function isExpired($key, $getContent = false)
    {
        if (is_array($key) && isset($key['expires'])) {
            return time() >= $key['expires'] && $key['ttl'] !== 0;
        }

        $content = $this->get($key);
        return $content && $getContent ? $content : $content && !$getContent ? true : false;
    }

    /**
     * @inheritDoc
     */
    public function exists($key)
    {
        return $this->fileSystem->exists($this->path($key));
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return $this->fileSystem->delete($this->path($key));
    }

    /**
     * @inheritDoc
     */
    public function destroy()
    {
        $return = [];
        $files = $this->fileSystem->find('*.php', $this->cacheDir);
        foreach($files as $file) {
            $return[] = $this->fileSystem->delete($file);
        }

        return $return;
    }

}