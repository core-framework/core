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


namespace Core\Contracts\Events;


interface Dispatcher
{
    /**
     * Add Subscribers
     *
     * @param string $subscriber
     * @return void
     */
    public function subscribe($subscriber);

    /**
     * Add Event listeners
     * 
     * @param string $name
     * @param Listener|string $listener
     * @param int $priority
     * @return void
     */
    public function on($name, $listener, $priority = 0);

    /**
     * Get all listener on an event(name)
     * 
     * @param string $name
     * @return \Closure
     */
    public function getListener($name);

    /**
     * Dispatcher has listener for event
     *
     * @param $name
     * @return bool
     */
    public function hasListener($name);

    /**
     * Dispatch event with payload
     *
     * @param $event
     * @param array $payload
     * @param b $breakOnFalse
     * @return mixed
     */
    public function dispatch($event, $payload = [], $breakOnFalse = true);
}