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


namespace Core\Contracts\Reactor;


interface Validator
{
    /**
     * The test to satisfy/check a given condition. Must return boolean true on success and false on failure
     *
     * @param mixed $subject Subject to validate
     * @return bool
     */
    public function test($subject);

    /**
     * Validate and execute success or failure callback
     *
     * @param mixed $subject
     * @param array|callable|\Closure $successCallback
     * @param array|callable|\Closure $failureCallback
     *
     * @return mixed
     */
    public function validate($subject, callable $successCallback = null, callable $failureCallback = null);

    /**
     * Success callback to be executed on success of validation test (i.e. condition has been met)
     *
     * @param null $subject
     * @return mixed
     */
    public function success($subject = null);

    /**
     * Reject callback to be executed on failure of validation test (i.e. condition has NOT been met)
     *
     * @param null $subject
     * @return mixed
     */
    public function reject($subject = null);
}