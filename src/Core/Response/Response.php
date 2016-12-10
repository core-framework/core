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

namespace Core\Response;

use Core\Contracts\Request\Request;
use Core\Contracts\Request\Request as RequestInterface;
use Core\Contracts\Response\Response as ResponseInterface;
use Core\Contracts\View;
use Core\Reactor\Cookie;
use Core\Reactor\HeaderCollection;

class Response implements ResponseInterface
{
    /**
     * Borrowed from @link https://github.com/symfony/symfony/blob/2.8/src/Symfony/Component/HttpFoundation/Response.php
     */
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2015-05-19).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    );
    /**
     * END of borrowed
     */

    /**
     * @var array
     */
    public static $mimeTypes = [
        'html' => ['text/html', 'application/xhtml+xml'],
        'txt' => ['text/plain'],
        'js' => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css' => ['text/css'],
        'json' => ['application/json', 'application/x-json'],
        'xml' => ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf' => ['application/rdf+xml'],
        'atom' => ['application/atom+xml'],
        'rss' => ['application/rss+xml'],
        'form' => ['application/x-www-form-urlencoded'],
        'pdf' => ['application/pdf'],
        'activex' => ['application/olescript']
    ];
    public static $cacheability = ['public', 'private', 'no-cache', 'only-if-cached'];
    public static $cacheRevalidations = ['must-revalidate', 'proxy-revalidate', 'immutable'];
    /**
     * @var bool
     */
    public $do_gzip_compression = false;
    /**
     * @var View $view
     */
    protected $view;
    /**
     * @var string $content
     */
    protected $content;
    /**
     * @var array $cookies
     */
    protected $cookies = [];
    /**
     * @var array|HeaderCollection $headers
     */
    protected $headers = [];
    /**
     * @var int $statusCode
     */
    protected $statusCode = 200;
    /**
     * @var string $statusCodeText
     */
    protected $statusCodeText = "";
    /**
     * @var string $httpVersion
     */
    protected $httpVersion = "1.1";
    /**
     * @var string $charset
     */
    protected $charset = 'UTF-8';
    /**
     * @var $file
     */
    protected $file;
    /**
     * @var bool $isDownload
     */
    protected $isDownload = false;
    /**
     * @var bool $useView
     */
    private $useView = false;
    /**
     * @var bool $contentIsSet
     */
    private $contentIsSet = false;

    /**
     * Response constructor.
     *
     * @param string $content
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct($content = '', $statusCode = self::HTTP_OK, array $headers = [])
    {
        if (!is_null($statusCode) && !is_int($statusCode)) {
            throw new \InvalidArgumentException("Status Code must be a valid Integer, " . gettype($statusCode) . " given.");
        }

        $this->setHeaders($headers);
        $this->setContent($content);
        $this->setStatusCode($statusCode);
        $this->setDefaults();

        return $this;
    }

    /**
     * @param $headers
     * @return Response
     */
    public function setHeaders(array $headers)
    {
        $this->headers = new HeaderCollection($headers);

        return $this;
    }

    /**
     * set defaults
     */
    protected function setDefaults()
    {
        if (!$this->headers->has('Connection')) {
            $this->setConnection();
        }
        if (!$this->headers->has('Date')) {
            $this->setDate(\DateTime::createFromFormat('U', time()));
        }
    }

    /**
     * Control options for the current connection and list of hop-by-hop response fields
     *
     * @param string $type
     * @return Response
     */
    public function setConnection($type = "keep-alive")
    {
        $valid = ["keep-alive", "close"];
        if (in_array($type, $valid)) {
            $this->header('Connection', $type);
        } else {
            throw new \InvalidArgumentException("Connection only supports 'keep-alive' & 'close' values.");
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function header($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $header => $value) {
                $this->headers->set($header, $value);
            }
        } else {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * Set Date header
     *
     * @param \DateTime $date
     * @return Response
     */
    public function setDate(\DateTime $date)
    {
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Date', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Use for immediate redirection
     *
     * @param $url
     * @param int $statusCode
     */
    public static function redirect($url, $statusCode = self::HTTP_FOUND)
    {
        if (!is_int($statusCode)) {
            throw new \InvalidArgumentException("Status Code must be of type Integer.");
        }

        header("Location: " . $url, true, $statusCode);
    }

    /**
     * @param $content
     * @param int $statusCode
     * @return Response
     */
    public static function create($content, $statusCode = self::HTTP_OK)
    {
        return new self($content, $statusCode);
    }

    /**
     * Get HTTP response status
     *
     * @return bool
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set HTTP response status
     *
     * @param int $code
     * @param null $text
     * @return Response
     */
    public function setStatusCode($code = self::HTTP_OK, $text = null)
    {
        $this->statusCode = $code = (int)$code;

        if ($this->isValidStatus()) {
            throw new \InvalidArgumentException("Invalid Status Code: " . gettype($code) . " given.");
        }

        if ($text === null) {
            $this->statusCodeText = isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : '';
        }

        $this->statusCodeText = $text;

        return $this;
    }

    /**
     * @param $charset
     *
     * @return Response
     */
    public function charset($charset = 'UTF-8')
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Returns true if status code is valid, else false
     *
     * @return bool
     */
    public function isValidStatus()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Get the http status text
     *
     * @return string
     */
    public function getStatusCodeText()
    {
        return $this->statusCodeText;
    }

    /**
     * Set the http status text
     *
     * @param $text
     * @return Response
     */
    public function setStatusCodeText($text)
    {
        $this->statusCodeText = $text;

        return $this;
    }

    /**
     * Set View object
     *
     * @param View $view
     * @return Response
     */
    public function view(View $view)
    {
        $this->view = $view;
        $this->useView = true;

        return $this;
    }

    /**
     * get header value if set, else returns false
     *
     * @param $key
     * @param $fail
     * @return bool
     */
    public function getHeader($key, $fail = false)
    {
        return $this->headers->has($key) ? $this->headers->get($key, $fail) : $fail;
    }

    /**
     * @return array|HeaderCollection
     */
    public function getHeaderCollection()
    {
        return $this->headers;
    }

    /**
     * Remove previously set header. Returns false if header not found.
     *
     * @param $key
     * @return bool
     */
    public function removeHeader($key)
    {
        if (isset($this->headers[$key])) {
            unset($this->headers[$key]);
            return true;
        }

        return false;
    }

    /**
     * Returns true if response content was set else false
     *
     * @return bool
     */
    public function getIsContentSet()
    {
        return $this->contentIsSet;
    }

    /**
     * @inheritdoc
     */
    public function setCookie(
        $name,
        $value = null,
        $domain = null,
        $expiresInMinutes = 0,
        $path = '/',
        $secure = false,
        $httpOnly = true
    ) {
        if (!is_numeric($expiresInMinutes)) {
            throw new \InvalidArgumentException('$expiresInMinutes must be an integer specifying the number of minutes into the future, after which the cookie expires');
        }

        if ($expiresInMinutes !== 0) {
            $expires = time() + ($expiresInMinutes * 60);
        } else {
            $expires = $expiresInMinutes;
        }

        $cookie = new Cookie($name, $value, $domain, $expires, $path, $secure, $httpOnly);
        $this->cookies[] = $cookie;

        return $cookie;
    }

    /**
     * Return file if set
     *
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set file
     *
     * @param $file
     * @return Response
     */
    public function setFile($file)
    {
        if (is_readable($file) === false) {
            throw new \InvalidArgumentException("Given File should be a valid and readable file.");
        }

        $this->file = $file;

        return $this;
    }

    /**
     * Set Access Control header
     *
     * @param $domain
     * @return Response
     */
    public function setAccessControl($domain = "*")
    {
        $this->header('Access-Control-Allow-Origin', $domain);

        return $this;
    }

    /**
     * Set Accept-Patch header
     *
     * @param string $contentType
     * @param string $charset
     * @return Response
     */
    public function setAcceptPatch($contentType = "text/html", $charset = "utf-8")
    {
        $this->header("Accept-Patch", "{$contentType};charset={$charset}");

        return $this;
    }

    /**
     * Set Accept Range header
     *
     * @param null $range
     * @return Response
     */
    public function setAcceptRange($range = null)
    {
        if (is_null($range)) {
            $this->header("Accept-Ranges", "none");
        } elseif (strpos($range, "-") !== false) {
            $this->header("Accept-Ranges", "bytes " . $range);
        } elseif ($range === "") {
            $this->header("Accept-Ranges", "bytes");
        } else {
            throw new \InvalidArgumentException("Incorrect value provided for Accept-Ranges header. Range must be string with '-' character or an empty string.");
        }

        return $this;
    }

    /**
     * Set proxy-cache Age header
     *
     * @param $seconds
     * @return Response
     */
    public function setAge($seconds)
    {
        if (!is_int($seconds)) {
            throw new \InvalidArgumentException("Seconds must be of type integer, " . gettype($seconds) . " given.");
        }
        $this->header('Age', $seconds);

        return $this;
    }

    /**
     * Valid actions for a specified resource. To be used for a 405 Method not allowed
     *
     * @param array $allowedMethods
     * @return Response
     */
    public function setAllow(array $allowedMethods)
    {
        $supported = [
            'GET',
            'HEAD',
            'OPTIONS',
            'PUT',
            'DELETE',
            'POST',
            'TRACE',
            'CONNECT',
            'CHECKOUT',
            'SHOWMETHOD',
            'LINK',
            'UNLINK',
            'CHECKIN',
            'TEXTSEARCH',
            'SPACEJUMP',
            'SEARCH'
        ];
        $str = '';
        foreach ($allowedMethods as $method) {
            $methodStr = strtoupper($method);
            if (in_array($methodStr, $supported)) {
                $str .= $methodStr . ', ';
            }
        }
        $methods = rtrim($str, ", ");

        $this->header('Allow', $methods);

        return $this;
    }

    /**
     * Tells all caching mechanisms from server to client whether they may cache this object. It is measured in seconds
     *
     * @param string $cacheability
     * @param int $expirationTime
     * @param string $expirationType
     * @param string $validationType
     * @return Response
     */
    public function setCacheControl(
        $cacheability = "public",
        $expirationTime = null,
        $expirationType = null,
        $validationType = null
    ) {
        if ($expirationTime && !$expirationType) {
            $expirationType = 'max-age';
        }
        $expires = $expirationType . '=' . $expirationTime;
        $this->headers->set('Cache-Control',
            $cacheability . ($expirationTime ? ', ' . $expires : '') . ($validationType ? ', ' . $validationType : ''));
        return $this;
    }

    /**
     * An opportunity to raise a "File Download" dialogue box for a known MIME type with binary format or suggest a filename for dynamic content. Quotes are necessary with special characters.
     *
     * @param string $type
     * @param array $params
     * @return Response
     */
    public function setContentDisposition($type = "inline", array $params = [])
    {
        $validTypes = [
            "inline",
            "attachment",
            "form-data",
            "signal",
            "alert",
            "icon",
            "render",
            "recipient-list-history",
            "session",
            "aib",
            "early-session",
            "recipient-list",
            "notification",
            "by-reference",
            "info-package",
            "recording-session"
        ];
        $validParams = [
            "filename",
            "creation-date",
            "modification-date",
            "read-date",
            "size",
            "name",
            "voice",
            "handling"
        ];

        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException("Given Content Disposition Type is not valid! Valid Types are - " . implode(',',
                    $validTypes) . ".");
        }

        if (!isset($type) || empty($type)) {
            $valueStr = $type;
        } else {
            $valueStr = $type . ";";
            foreach ($params as $key => $val) {
                if (in_array($key, $validParams)) {
                    if ($key === 'filename') {
                        $this->file = $val;
                    }
                    $valueStr .= " " . $key . "=\"" . $val . "\";";
                } else {
                    throw new \InvalidArgumentException("Given Parameter is not valid! Valid Parameters are - " . implode(',',
                            $validParams) . ".");
                }
            }
            $valueStr = rtrim($valueStr, ";");
        }

        $this->header('Content-Disposition', $valueStr);

        return $this;
    }

    /**
     * The type of encoding used on the data. See HTTP compression.
     *
     * @link https://en.wikipedia.org/wiki/HTTP_compression
     * @param $type
     * @return Response
     */
    public function setContentEncoding($type = null)
    {
        if (is_null($type) && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'],
                'gzip') !== false
        ) {
            $type = "gzip";
        }

        $this->do_gzip_compression = true;

        $this->header('Content-Encoding', $type);

        return $this;
    }

    /**
     * The natural mapper or languages of the intended audience for the enclosed content
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.12
     * @param string $language
     * @return Response
     */
    public function setContentLanguage($language = "en")
    {
        $this->header('Content-Language', $language);

        return $this;
    }

    /**
     * The Content-Location entity-header field MAY be used to supply the resource Location for the entity enclosed in the message when that entity is accessible from a Location separate from the requested resource’s URI.
     *
     * @param $path
     * @return Response
     */
    public function setContentLocation($path)
    {
        $this->header('Content-Location', $path);
        return $this;
    }

    /**
     * Where in a full body message this partial message belongs
     *
     * @param $start
     * @param $end
     * @param $total
     * @param string $unit
     * @return Response
     */
    public function setContentRange($start, $end, $total, $unit = "bytes")
    {
        if (!is_int($start) || !is_int($end) || !is_int($total)) {
            throw new \InvalidArgumentException("Argument passed must be of type Integer. One or more non-integers arguments were given.");
        }

        $this->header('Content-Range', "{$unit} {$start}-{$end}/{$total}");

        return $this;
    }

    /**
     * Add MIME Types
     *
     * @param $type
     * @param $value
     * @return $this
     */
    public function addContentType($type, $value)
    {
        if (is_array($type)) {
            array_merge(self::$mimeTypes, $type);
        } else {
            self::$mimeTypes[$type] = $value;
        }

        return $this;
    }

    /**
     * Get MIME Type based on type name
     *
     * @param $type
     * @param int $index
     * @return mixed
     */
    public function getMimeType($type, $index = 0)
    {
        if (!isset(self::$mimeTypes[$type][$index])) {
            throw new \InvalidArgumentException("Index value:{$index} for {$type} does not exist.");
        }
        return self::$mimeTypes[$type][$index];
    }

    /**
     * Use for lazy redirection with other additional header parameters
     *
     * @param $location
     * @param int $statusCode
     * @return Response
     */
    public function setRedirect($location, $statusCode = self::HTTP_FOUND)
    {
        if (!is_int($statusCode)) {
            throw new \InvalidArgumentException("Status Code must be of type Integer.");
        }

        $this->statusCode = $statusCode;
        $this->header('Location', $location);
        return $this;
    }

    /**
     * @return bool
     * @throws \HttpEncodingException
     */
    public function send()
    {
        $this->gzipCheckAndInit();
        $this->sendHeaders();
        $this->sendOutput();
    }

    /**
     * Initiate gzip ob_start handler if enabled
     *
     * @throws \HttpEncodingException
     */
    public function gzipCheckAndInit()
    {
        if ($this->do_gzip_compression === true && extension_loaded('zlib') === true) {
            ob_start('ob_gzhandler');
            ob_implicit_flush(0);
            $this->vary('Accept-Encoding');
        } elseif ($this->do_gzip_compression === true && extension_loaded('zlib') === false) {
            throw new \HttpEncodingException("Response set to use gzip compression, but 'zlib' extension not loaded or not found.");
        }
    }

    /**
     * @param $header
     * @return $this
     */
    public function vary($header)
    {
        if (is_array($header)) {
            $header = implode(',', $header);
        }

        $this->headers->set('Vary', $header);

        return $this;
    }

    /**
     * Output headers
     *
     * @return Response
     */
    protected function sendHeaders()
    {
        $headers = $this->headers;

        // headers already send
        if (headers_sent()) {
            return $this;
        }

        // Lazy redirect
        if (isset($headers['Location'])) {
            header("Location: " . $headers['Location'], true, $this->statusCode);
            return $this;
        }

        // status
        header("HTTP/{$this->httpVersion} {$this->statusCode} {$this->statusCodeText}", true, $this->statusCode);

        // Lazy(/consolidated) setting of headers
        foreach ($headers as $key => $val) {
            header("{$this->headers->getOriginalName($key)}: {$val}", true, $this->statusCode);
        }

        // cookies
        foreach ($this->cookies as $cookie) {
            /** @var Cookie $cookie */
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpires(), $cookie->getPath(),
                $cookie->getDomain(), $cookie->getSecure(), $cookie->getHttpOnly());
        }

        return $this;
    }

    /**
     * Renders output
     */
    protected function sendOutput()
    {
        if ($this->useView === true && !$this->content instanceof View) {
            $this->content = $this->view->fetch();
        } elseif ($this->file && !$this->isDownload) {
            $this->content = file_get_contents($this->file);
        }

        echo $this->content;

        if ($this->file && $this->isDownload) {
            readfile($this->file);
        }

        if ($this->do_gzip_compression === true) {
            $this->sendGzipCompressed();
        }
    }

    /**
     * gzip Output compression
     *
     * @return bool
     */
    private function sendGzipCompressed()
    {
        //
        // Borrowed from php.net!
        //
        $gzip_contents = ob_get_contents();
        ob_end_clean();

        $gzip_size = strlen($gzip_contents);
        $gzip_crc = crc32($gzip_contents);

        $gzip_contents = gzcompress($gzip_contents, 9);
        $gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

        echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
        echo $gzip_contents;
        echo pack('V', $gzip_crc);
        echo pack('V', $gzip_size);

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return
            "HTTP/{$this->httpVersion} {$this->statusCode} {$this->statusCodeText}\r\n" .
            $this->headers . "\r\n" .
            $this->getContent();
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set Response Content
     *
     * @param string $content
     * @return Response
     */
    public function setContent($content = '')
    {
        if (!is_string($content) && !is_array($content) && !$content instanceof View && !$content instanceof \Serializable) {
            throw new \InvalidArgumentException("Content must be of type String, Array OR objects that can be Serialized (or implement __toString() method), " . gettype($content) . " given.");
        }

        if ($content instanceof View) {
            $this->view($content);
            $content = $content->fetch();
        } elseif ($content instanceof \Serializable) {
            $content = $content->serialize();
        } elseif (is_array($content)) {
            $this->header('Content-Type', 'application/json');
            $content = json_encode($content);
        }

        $this->contentIsSet = true;
        $this->content = (string)$content;

        return $this;
    }

    /**
     * @param RequestInterface $request
     */
    public function format(RequestInterface $request)
    {
        $headers = $this->headers;
        if (!$headers->has('Content-Type')) {
            $this->contentType();
        }

        if ($request->getHttpMethod() === 'HEAD') {
            $length = $headers->get('Content-Length');
            $this->setContent();
            if ($length) {
                $this->setContentLength($length);
            }
        }

        if ($request->server('SERVER_PROTOCOL') !== 'HTTP/1.1') {
            $this->setHttpVersion('1.0');
        }

        if ($this->getHttpVersion() === '1.0' && $headers->get('Cache-Control') === 'no-cache') {
            $this->headers->set('Pragma', 'no-cache');
            $this->headers->set('Expires', -1);
        }
    }

    /**
     * The MIME type of the content
     *
     * @param string $mimeType
     * @return Response
     */
    public function contentType($mimeType = "text/html")
    {
        $charset = $this->charset;
        if ($mimeType === "") {
            throw new \InvalidArgumentException("Given arguments cannot be empty.");
        }

        if (!is_string($mimeType)) {
            throw new \InvalidArgumentException("Given arguments must of type String.");
        }

        if (strpos($mimeType, '/') === false) {
            $mimeType = self::$mimeTypes[$mimeType] ?: $mimeType;
        }

        if (stripos($mimeType, 'text/') === 0) {
            $this->header('Content-Type', "{$mimeType}; charset={$charset}");
        } else {
            $this->header('Content-Type', $mimeType);
        }

        return $this;
    }

    /**
     * The length of the response body in octets (8-bit bytes)
     *
     * @param null $length
     * @return Response
     */
    public function setContentLength($length = null)
    {
        if (is_null($length) && isset($this->file) && is_readable($this->file)) {
            $length = (int)filesize($this->file);
        } elseif (is_null($length)) {
            throw new \InvalidArgumentException("Length not given. The file must be set using the setFile method before calling this method without a parameter");
        }

        if (!is_int($length)) {
            throw new \InvalidArgumentException("Length must be a valid Integer, " . gettype($length) . " given.");
        }

        $this->header('Content-Length', $length);

        return $this;
    }

    /**
     * @return string
     */
    public function getHttpVersion()
    {
        return $this->httpVersion;
    }

    /**
     * @param $version
     * @return Response
     */
    public function setHttpVersion($version)
    {
        $this->httpVersion = $version;

        return $this;
    }

    /**
     * @param $filePath
     * @param $filename
     * @param array $headers
     */
    public function download($filePath, $filename, $headers = [])
    {
        if (!is_readable($filePath)) {
            throw new \RuntimeException("Given file could not be found or is not readable");
        }

        $filename = $filename ?: basename($filePath);

        $_headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Transfer-Encoding' => 'Binary',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $headers = array_merge($headers, $_headers);
        $this->addHeaders($headers);
        $this->isDownload = true;
        $this->file = $filePath;
    }

    /**
     * @inheritdoc
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }

        return $this;
    }

    /**
     * @param $filePath
     * @param array $headers
     * @return Response
     */
    public function file($filePath, $headers = [])
    {
        if (!is_readable($filePath)) {
            throw new \RuntimeException("Given file could not be found or is not readable");
        }

        $this->addHeaders($headers);
        $this->file = $filePath;

        return $this;
    }

    /**
     * Disable HTTP Caching
     */
    public function disableCache()
    {
        $this->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        return $this;
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $cacheability
     * @param null $revalidation
     */
    public function cache($from = '-1 minute', $to = '+1 day', $cacheability = 'public', $revalidation = null)
    {
        if (!in_array($cacheability, self::$cacheability)) {
            throw new \InvalidArgumentException('Invalid cache access value given: ' . $cacheability);
        }
        if ($revalidation !== null && !in_array($revalidation, self::$cacheRevalidations)) {
            throw new \InvalidArgumentException('Invalid cache revalidation value given: ' . $revalidation);
        }
        $fromObj = new \DateTime($from);
        $toObj = new \DateTime($to);
        $maxAge = $fromObj->format('U') - $toObj->format('U');
        $interval = $fromObj->diff($toObj);
        $expires = $fromObj->add($interval);
        $this->header([
            'Cache-Control' => $cacheability . ', max-age=' . $maxAge . ($revalidation ? ', ' . $revalidation : ''),
            'Expires' => $expires->format('D, d M Y H:t:s') . ' GMT',
            'Last-Modified' => $fromObj->format('D, d M Y H:t:s') . ' GMT'
        ]);
    }

    /**
     * @param string $time
     * @return $this
     */
    public function expires($time = '+1 day')
    {
        $t = new \DateTime($time);
        $this->headers->set('Expires', $t->format('D, d M Y H:t:s') . ' GMT');

        return $this;
    }

    /**
     * @param string $time
     * @return $this
     */
    public function lastModified($time = 'now')
    {
        $t = new \DateTime($time);
        $this->headers->set('Last-Modified', $t->format('D, d M Y H:t:s') . ' GMT');

        return $this;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isModified(Request $request)
    {
        if ($t1 = $request->headers('Last-Modified') && $t2 = $this->headers->get('Last-Modified')) {
            $t1 = new \DateTime($t1);
            $t2 = new \DateTime($t2);
            return $t1 <= $t2;
        }

        return false;
    }
}