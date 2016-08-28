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


namespace Core\Session\Handlers;


class MemCacheSessionHandler implements \SessionHandlerInterface
{

    protected $memcache;
    protected $ttl;

    /**
     * MemCacheSessionHandler constructor.
     *
     * @param \Memcache $memcache
     * @param int $ttl
     */
    public function __construct(\Memcache $memcache, $ttl = 86400)
    {
        $this->memcache = $memcache;
        $this->ttl = $ttl;
    }


    /**
     * @inheritDoc
     */
    public function close()
    {
        $this->memcache->close();
    }

    /**
     * @inheritDoc
     */
    public function destroy($sessionId)
    {
        $this->memcache->delete($sessionId);
    }

    /**
     * @inheritDoc
     */
    public function gc($maxLife)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read($sessionId)
    {
        return $this->memcache->get($sessionId) ?: '';
    }

    /**
     * @inheritDoc
     */
    public function write($sessionId, $data)
    {
        return $this->memcache->set($sessionId, $data, 0, time() + $this->ttl);
    }

}