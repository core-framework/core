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

namespace Core\Contracts\Response;

use Core\Contracts\View;

interface Response
{
    /**
     * Set Response Content
     *
     * @param null $content
     * @supported array || string || serializable
     * @return void
     */
    public function setContent($content = null);

    /**
     * Check if content is set
     *
     * @return mixed
     */
    public function getIsContentSet();

    /**
     * Set status code for current response
     *
     * @param int $code
     * @return void
     */
    public function setStatusCode($code = 200);


    /**
     * Set view object associated with current response
     *
     * @param View $view
     * @return void
     */
    public function setView(View $view);

    /**
     * Add Header(s) for current response
     *
     * @param $key
     * @param $value
     * @return void
     */
    public function addHeader($key, $value);

    /**
     * Get a previously set header
     *
     * @param $key
     * @return mixed
     */
    public function getHeader($key);

    /**
     * Remove previously set header
     *
     * @param $key
     * @return mixed
     */
    public function removeHeader($key);

    /**
     * Method to send computed response to Client (browser)
     *
     * @return mixed
     */
    public function send();

}