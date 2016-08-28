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


interface Cache
{

    /**
     * Cache File Contents
     *
     * @param string $key
     * @param array|\Serializable|Cacheable|string $payload
     * @param int $ttl In Seconds
     * @return bool
     */
    public function put($key, $payload, $ttl = 0);

    /**
     * Get Cached File Contents
     *
     * @param string $key
     * @return false|mixed
     */
    public function get($key);

    /**
     * Check if given Cache exists
     *
     * @param string $key
     * @return bool
     */
    public function exists($key);

    /**
     * Delete given Cache file
     *
     * @param string $key
     * @return bool
     */
    public function delete($key);

    /**
     * Clear all cache (complete cache directory)
     *
     * @return array
     */
    public function destroy();

}