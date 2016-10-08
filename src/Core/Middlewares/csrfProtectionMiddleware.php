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


namespace Core\Middlewares;

use Core\Contracts\Middleware;
use Core\Contracts\Request\Request;
use Core\Contracts\Response\Response;
use Core\Contracts\Router\Router;
use Core\Exceptions\HttpException;
use Core\Facades\Cache;
use Core\Facades\View;

class csrfProtectionMiddleware implements Middleware
{

    /**
     * @var string $token
     */
    protected $token;

    /**
     * @inheritdoc
     */
    public function run(Router $router, \Closure $next)
    {
        $this->validateToken($router);
        $response = $next();
        if ($response instanceof Response) {
            $response->addHeader('X-CSRF-Token', $this->getCSRFToken());
        }

        return $response;
    }

    /**
     * @param Router $router
     */
    protected function validateToken(Router $router)
    {
        $request = $router->getRequest();
        $httpMethod = $request->getHttpMethod();
        if ($httpMethod !== 'GET' && $router->getCurrentRoute()->mustBeCsrfProtected()) {
            $csrfToken = $this->getCsrfFromRequest($request);
            if (!$this->verifyToken($csrfToken)) {
                throw new HttpException("Given X-CSRF-TOKEN does not match!", 422);
            }
        } else {
            $this->generateToken();
        }
    }

    /**
     * Gets csrf token from Request headers|body
     *
     * @param Request $request
     * @return array|\Core\Reactor\DataCollection
     */
    private function getCsrfFromRequest(Request $request)
    {
        $token = $request->headers('X-CSRF-TOKEN');
        if (empty($token)) {
            $body = $request->body();
            if (isset($body->csrfToken)) {
                $token = $body->csrfToken;
            }
        }
        return $token;
    }

    /**
     * Gets stored(previously generated) csrf token
     *
     * @return string
     */
    protected function getCSRFToken()
    {
        if (!$this->token) {
            if (session_status() === PHP_SESSION_NONE) {
                $this->token = Cache::get('csrf-token');
            } else {
                $this->token = isset($_SESSION['csrf-token']) ? $_SESSION['csrf-token'] : false;
            }
            if ($this->token === false) {
                $this->token = '';
            }
        }

        return $this->token;
    }

    /**
     * Generate new csrf token
     *
     * @return string
     */
    protected function generateToken()
    {
        $this->token = md5(uniqid(microtime(), true));
        if (session_status() === PHP_SESSION_NONE) {
            $t = Cache::put('csrf-token', $this->token, 2000);
            $r = Cache::get('csrf-token');
        } else {
            $_SESSION['csrf-token'] = $this->token;
        }
        $this->setCsrfInView($this->token);
        return $this->token;
    }

    /**
     * Set csrf-token variable in template engine
     *
     * @param $token
     */
    private function setCsrfInView($token)
    {
        try {
            View::set('csrf-token', $token);
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * Compares given token with token existing in session/cache
     *
     * @param $token
     * @return bool
     */
    protected function verifyToken($token)
    {
        $_token = $this->getCSRFToken();
        return $this->hash_compare($_token, $token);
    }

    /**
     * Compares two hashes bit by bit
     *
     * @param $a
     * @param $b
     * @return bool
     */
    private function hash_compare($a, $b) {
        if (!is_string($a) || !is_string($b)) {
            return false;
        }

        $len = strlen($a);
        if ($len !== strlen($b)) {
            return false;
        }

        $status = 0;
        for ($i = 0; $i < $len; $i++) {
            $status |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $status === 0;
    }

}