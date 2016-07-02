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


namespace Core\Contracts\Request;


interface Request
{

    /**
     * Creates request instance from PHP Globals
     *
     * @return Request
     */
    public static function createFromGlobals();

    /**
     * Returns Request body
     *
     * @return string
     */
    public function body();


    /**
     * Get our headers from our server data collection
     *
     * PHP is weird... it puts all of the HTTP request
     * headers in the $_SERVER array. This handles that
     *
     * @return array
     */
    public function getHeaders();

    
    /**
     * Returns true if domain is secure
     *
     * @return bool
     */
    public function isSecure();
    
    
    /**
     * Returns the requester's IP
     *
     * @return mixed
     */
    public function ip();
    
    
    /**
     * Returns the User Agent string
     *
     * @return mixed
     */
    public function userAgent();

    /**
     * Returns true if current request is an ajax call
     * 
     * @return bool
     */
    public function isAjax();
    
    /**
     * Returns an array of server info
     *
     * @return array
     */
    public function getServer();
    
    /**
     * Returns an array of Cookies set
     *
     * @return array
     */
    public function getCookies();
    
    /**
     * Returns the httpMethod used for the current request
     *
     * @return string
     */
    public function getHttpMethod();
    
    /**
     * Sets the http Method
     *
     * @param $httpMethod
     * @return $this
     */
    public function setHttpMethod($httpMethod);
    
    /**
     * Returns the url path/query string
     *
     * @return string
     */
    public function getPath();
    
    /**
     * Set the url path/query string
     *
     * @param string $path
     * @return void
     */
    public function setPath($path);
    
    
    
}