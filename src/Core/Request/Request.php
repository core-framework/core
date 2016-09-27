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

namespace Core\Request;

use Core\Contracts\Request\Request as RequestInterface;
use Core\Reactor\DataCollection;
use Core\Reactor\HttpParameter;

/**
 * The class that handles the incoming request to server
 *
 * <code>
 *  $request = Container::get('Request');
 * </code>
 *
 * @package Core\Request
 * @version $Revision$
 * @license http://creativecommons.org/licenses/by-sa/4.0/
 * @link http://coreframework.in
 * @author Shalom Sam <shalom.s@coreframework.in>
 */
class Request implements RequestInterface
{
    public static $validHttpMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'COPY',
        'HEAD',
        'OPTIONS',
        'LINK',
        'UNLINK',
        'PURGE',
        'LOCK',
        'UNLOCK',
        'PROPFIND',
        'VIEW'
    ];

    /**
     * @var string The URL/query string (relative path)
     */
    protected $path;

    /**
     * @var string The request httpMethod .i.e. GET, POST, PUT and DELETE
     */
    protected $httpMethod;
    /**
     * @var array Contains the sanitized array of the global $_GET variable
     */
    public $GET;
    /**
     * @var array Contains the sanitized array of the global $_POST variable
     */
    public $POST;
    /**
     * @var array Contains the $_SERVER data from the request
     */
    public $server;
    /**
     * @var array Request Headers
     */
    public $headers;
    /**
     * @var array Contains the cookie data from the request
     */
    public $cookies;
    /**
     * @var array Contains $_FILES data
     */
    public $files;
    /**
     * @var null|string Contains the Request Body
     */
    public $body;
    /**
     * @var bool Defines if operating in development mode
     */
    public $devMode = false;
    /**
     * @var bool Defines if Request is Ajax
     */
    protected $isAjax = false;

    /**
     * @var array $jsonArr
     */
    protected $jsonArr;

    protected static $http_header_prefix = 'HTTP_';
    protected static $http_nonprefixed_headers = array(
        'CONTENT_LENGTH',
        'CONTENT_TYPE',
        'CONTENT_MD5',
    );

    /**
     * @var array An array of illegal characters
     */
    private $illegal = [
        '$',
        '*',
        '"',
        '\'',
        '<',
        '>',
        '^',
        '(',
        ')',
        '[',
        ']',
        '\\',
        '!',
        '~',
        '`',
        '{',
        '}',
        '|',
        '%',
        '+',
        '?php'
    ];

    /**
     * Request constructor.
     *
     * @param array $GET
     * @param array $POST
     * @param array $server
     * @param array $cookies
     * @param array $files
     * @param string $body
     */
    public function __construct(
        array $GET = [],
        array $POST = [],
        array $server = [],
        array $cookies = [],
        array $files = [],
        $body = null
    ) {
        $this->GET = new DataCollection($GET);
        $this->POST = new DataCollection($POST);
        $this->server = new DataCollection($server);
        $this->headers = new HttpParameter($this->getHeaders());
        $this->cookies = new DataCollection($cookies);
        $this->files = new DataCollection($files);
        $this->body = isset($body) ? (string)$body : null;

        $this->getServerRequest();
    }

    /**
     * @return Request
     */
    public static function createFromGlobals()
    {
        return new static($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
    }

    /**
     * Gets the request body
     *
     * @return string
     */
    public function body()
    {
        // Only get it once
        if (null === $this->body) {
            $this->body = @file_get_contents('php://input');
        }
        return $this->body;
    }

    /**
     * Returns true if domain is secure
     *
     * @return bool
     */
    public function isSecure()
    {
        $https = $this->server['HTTPS'];
        return !empty($https) && 'off' !== strtolower($https);
    }

    /**
     * @inheritdoc
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    public function getHost()
    {
        if (!$host = $this->headers->get('HOST')) {
            if (!$host = $this->server->get('SERVER_NAME')) {
                $host = $this->server->get('SERVER_ADDR', '');
            }
        }

        // trim and remove port number from host
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        // check to see if it contain forbidden characters (see RFC 952 and RFC 2181)
        if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
            throw new \UnexpectedValueException(sprintf('Invalid Host "%s"', $host));
        }

        return $host;
    }

    /**
     * Returns the requester's IP
     *
     * @return mixed
     */
    public function ip()
    {
        return isset($this->server['REMOTE_ADDR']) ? $this->server['REMOTE_ADDR'] : '127.0.0.1';
    }

    /**
     * Returns the User Agent string
     *
     * @return mixed
     */
    public function userAgent()
    {
        return isset($this->server['USER_AGENT']) ? $this->server['USER_AGENT'] : false;
    }

    /**
     * @inheritdoc
     */
    public function isAjax()
    {
        return $this->isAjax;
    }

    /**
     * @inheritdoc
     */
    public function server($key = null, $default = false)
    {
        if (!empty($key)) {
            return $this->server->get($key, $default);
        }
        return $this->server;
    }


    /**
     * Get our headers from our server data collection
     *
     * PHP is weird... it puts all of the HTTP request
     * headers in the $_SERVER array. This handles that
     *
     * @return array
     */
    public function getHeaders()
    {
        // Define a headers array
        $headers = array();
        foreach ($this->server as $key => $value) {
            // Does our server attribute have our header prefix?
            if (self::hasPrefix($key, self::$http_header_prefix)) {
                // Add our server attribute to our header array
                $headers[substr($key, strlen(self::$http_header_prefix))] = $value;
            } elseif (in_array($key, self::$http_nonprefixed_headers)) {
                // Add our server attribute to our header array
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    /**
     *
     */
    private function getServerRequest()
    {
        //get httpMethod
        if (isset($this->server['REQUEST_METHOD'])) {
            $this->setHttpMethod($this->server['REQUEST_METHOD']);
        } elseif (isset($this->server['HTTP_X_HTTP_METHOD'])) {
            $this->setHttpMethod($this->server['HTTP_X_HTTP_METHOD']);
        } else {
            $this->setHttpMethod("GET");
        }

        if (strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest') {
            $this->isAjax = true;
        }

        $this->checkInput();

        //path
        $this->path = $this->getRequestUri() === "" ? '/' : $this->getRequestUri();
    }


    /**
     * The following method is derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     *
     * @inheritdoc
     */
    public function getRequestUri()
    {
        $requestUri = '';

        if ($this->headers->has('X_ORIGINAL_URL')) {
            // IIS with Microsoft Rewrite Module
            $requestUri = $this->headers->get('X_ORIGINAL_URL');

        } elseif ($this->headers->has('X_REWRITE_URL')) {
            // IIS with ISAPI_Rewrite
            $requestUri = $this->headers->get('X_REWRITE_URL');

        } elseif ($this->server->get('IIS_WasUrlRewritten') == '1' && $this->server->get('UNENCODED_URL') != '') {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = $this->server->get('UNENCODED_URL');

        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');
            // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path, only use URL path
            $schemeAndHttpHost = $this->getSchemeAndHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ('' != $this->server->get('QUERY_STRING')) {
                $requestUri .= '?'.$this->server->get('QUERY_STRING');
            }
        }

        // normalize the request URI to ease creating sub-requests from this request
        $this->server->set('REQUEST_URI', $requestUri);

        return $requestUri;
    }

    /**
     * Gets the complete domain name with scheme (http||https)
     *
     * @return string
     */
    public function getSchemeAndHost()
    {
        return $this->getScheme().'://'.$this->getHost();
    }

    /**
     * @param array $array
     * @return string
     */
    public function sanitizeArray(array $array)
    {
        $sanitized = [];
        foreach ($array as $key => $val) {

            if (is_array($val)) {
                $sanitized[$key] = $this->sanitizeArray($val);
                continue;
            }

            $sanitized[$key] = $this->sanitize($key, $val);
        }

        return $sanitized;
    }

    /**
     * @param $type
     * @param $value
     * @return string
     */
    public function sanitize($type, $value)
    {
        switch ($type) {
            case 'email':
                return htmlentities(
                    filter_var(trim($value), FILTER_SANITIZE_EMAIL),
                    ENT_COMPAT,
                    'UTF-8',
                    false
                );
                break;

            case 'phone':
            case 'mobile':
                return htmlentities(
                    filter_var(trim($value), FILTER_SANITIZE_NUMBER_INT),
                    ENT_COMPAT,
                    'UTF-8',
                    false
                );
                break;

            case 'data':
                return htmlentities(filter_var(trim($value), FILTER_UNSAFE_RAW));
                break;

            default:
                return htmlentities(
                    filter_var(trim($value), FILTER_SANITIZE_STRING),
                    ENT_COMPAT,
                    'UTF-8',
                    false
                );
                break;
        }
    }

    /**
     * Sanitize inputs
     *
     * @param $data
     * @return array
     */
    public function inputSanitize($data)
    {
        $sanitizedData = [];
        foreach ($data as $key => $val) {

            if (is_array($val)) {
                $sanitizedData[$key] = $this->sanitizeArray($val);
                continue;
            }

            $sanitizedData[$key] = $this->sanitize($key, $val);
        }

        return $sanitizedData;
    }

    /**
     * check for input stream
     */
    protected function checkInput()
    {
        $postData = file_get_contents("php://input");
        if (!empty($postData) && is_array($postData)) {
            $postData = $this->inputSanitize($postData);
            $this->POST['data'] = json_decode($postData, JSON_OBJECT_AS_ARRAY);
        } elseif (!empty($postData) && is_string($postData)) {
            if ($this->httpMethod === 'put') {
                parse_str($postData, $this->POST['data']);
                $this->POST['data'] = $this->inputSanitize($this->POST['data']);
            }
        }
    }

    /**
     * @param $key
     * @param bool $default
     * @return array|bool|mixed
     */
    public function input($key = null, $default = false)
    {
        $method = $this->getHttpMethod();
        if (method_exists($this, strtoupper($method))) {
            return $this->{strtoupper($method)}($key, $default);
        }
        return $default;
    }

    /**
     * @return array|DataCollection
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Returns the httpMethod used for the current request
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Sets the http Method
     *
     * @param $httpMethod
     * @return $this
     */
    public function setHttpMethod($httpMethod)
    {
        if (!in_array(strtoupper($httpMethod), static::$validHttpMethods)) {
            throw new \InvalidArgumentException("Unknown method: {$httpMethod} given.");
        }

        $this->httpMethod = strtoupper($httpMethod);
        return $this;
    }

    /**
     * Returns the url path/query string
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Quickly check if a string has a passed prefix
     *
     * @param string $string The string to check
     * @param string $prefix The prefix to test
     * @return boolean
     */
    public static function hasPrefix($string, $prefix)
    {
        if (strpos($string, $prefix) === 0) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function GET($key = null, $default = false)
    {
        if (!empty($key)) {
            return $this->GET->get($key, $default);
        }
        return $this->GET;
    }

    /**
     * @inheritdoc
     */
    public function POST($key = null, $default = false)
    {
        if (!empty($key)) {
            return $this->POST->get($key, $default);
        }
        return $this->POST;
    }

    public function PUT($key = null, $default = false)
    {
        if (!empty($key)) {
            $this->POST->get('data.'.$key, $default);
        }
        return $this->POST->get('data', $default);
    }

    public function DELETE($key = null, $default = false)
    {
        if (!empty($key)) {
            $this->POST->get('data.'.$key, $default);
        }
        return $this->POST->get('data', $default);
    }

    /**
     * @inheritdoc
     */
    public function cookies($key = null, $default = false)
    {
        if (!empty($key)) {
            return $this->cookies->get($key, $default);
        }
        return $this->cookies;
    }

    /**
     * @inheritdoc
     */
    public function headers($key = null)
    {
        if (!empty($key)) {
            return $this->headers->get($key, false);
        }
        return $this->headers;
    }

    public function isJson()
    {
        return strContains('json', $this->headers->get('Content-Type'));
    }

    public function json($key = null, $default = false)
    {
        if (!$this->isJson()) {
            return $default;
        }

        if (!$this->jsonArr) {
            $this->jsonArr = new DataCollection(json_decode($this->body, JSON_OBJECT_AS_ARRAY));
        }

        return $this->jsonArr->get($key, $default);
    }
}
