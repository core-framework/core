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

namespace Core\Reactor\Http\Psr7;


use Core\Contracts\Http\Psr7\Uri as UriInterface;

class Uri implements UriInterface
{
    protected static $schemes = ['http' => 80, 'https' => 443];
    protected $uri;
    protected $scheme = '';
    protected $host = '';
    protected $port;
    protected $user = '';
    protected $pass = '';
    protected $path = '';
    protected $query = '';
    protected $fragment = '';

    /**
     * Uri constructor.
     * @param $uri
     */
    public function __construct($uri = '')
    {
        if ($uri != null) {
            $this->uri = $uri;
            $this->parseUri($uri);
        }
    }

    /**
     * @param string $uri
     */
    protected function parseUri($uri = '')
    {
        if ($uri != null) {
            $uriParts = parse_url($uri);
            if ($uriParts === false) {
                throw new \InvalidArgumentException('Malformed URL: ' . $uri);
            }
            $this->applyUriParts($uriParts);
        }
    }

    /**
     * @param array $uriParts
     */
    public function applyUriParts(array $uriParts)
    {
        foreach ($uriParts as $key => $value) {
            if (method_exists($this, $method = 'set' . ucfirst($key))) {
                $this->{$method}($value);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = strtolower($scheme);
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param string $pwd
     */
    public function setPass($pwd)
    {
        $this->pass = $pwd;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority()
    {
        if (!isset($this->host)) {
            return '';
        }

        $authority = $this->host;
        if (!empty($this->user)) {
            $authority = $this->getUserInfo() . '@' . $authority;
        }

        if (isset($this->port)) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo()
    {
        return (isset($this->pass) && isset($this->user)) ? $this->user . ':' . $this->pass : $this->user;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @inheritDoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param int|string $port
     */
    public function setPort($port)
    {
        $port = (int)$port;
        if (1 > $port || 65535 < $port) {
            throw new \InvalidArgumentException(
                sprintf('Invalid port: %d. Must be between 1 and 65535', $port)
            );
        }

        if ($this->isStandardPort($port) === false) {
            $this->port = $port;
        }
    }

    public function isStandardPort($port)
    {
        return static::$schemes[$this->scheme] === $port;
    }

    /**
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $this->filter($path);
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $str
     * @return string
     */
    protected function filter($str)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatch'],
            $str
        );
    }

    /**
     * @param array $match
     * @return string
     */
    public function rawurlencodeMatch(array $match)
    {
        return rawurlencode($match[0]);
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $this->filter($query);
    }
    
    /**
     * @inheritDoc
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $fragment
     */
    public function setFragment($fragment)
    {
        $this->fragment = $this->filter($fragment);
    }

    /**
     * @inheritDoc
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme)
    {
        $scheme = strtolower($scheme);
        
        if ($this->scheme === $scheme) {
            return $this;
        }

        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }
    
    

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        $info = $user;
        if ($password) {
            $info .= ':' . $password;
        }
        
        if ($this->getUserInfo() === $info) {
            return $this;
        }
        
        $clone = clone $this;
        $clone->user = $user;
        if ($password) {
            $clone->pass = $password;
        }
        
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        if ($this->host = $host) {
            return $this;
        }
        
        $clone = clone $this;
        $clone->host = $host;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        if ($this->port == (int) $port) {
            return $this;
        }

        $clone = clone $this;
        $clone->setPort($port);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Invalid path provided; must be a string');
        }

        $path = $this->filter($path);

        if ($this->path === $path) {
            return $this;
        }

        $clone = clone $this;
        $clone->path = $path;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        if (!is_string($query)) {
            throw new \InvalidArgumentException("Query string must be of type string. {${gettype($query)}} given.");
        }

        $query = $this->filter(ltrim($query, '?'));

        if ($this->query === $query) {
            return $this;
        }

        $clone = clone $this;
        $clone->query = $query;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        if (!is_string($fragment)) {
            throw new \InvalidArgumentException("Query string must be of type string. {${gettype($fragment)}} given.");
        }
        
        $fragment = $this->filter(ltrim($fragment, '#'));
        
        if ($this->fragment === $fragment) {
            return $this;
        }
        
        $clone = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return static::buildUriStr($this);
    }

    protected static function buildUriStr(UriInterface $uri)
    {
        $uriStr = '';

        if (!empty($scheme = $uri->getScheme())) {
            $uriStr .= $scheme . ':';
        }

        if (!empty($auth = $uri->getAuthority())) {
            if ($scheme != null) {
                $uriStr .= '//';
            }
            $uriStr .= $auth;
        }

        $path = $uri->getPath();
        if ($path != null) {
            if ($uri->getHost() != null && substr($path, 0, 1) !== '/') {
                $uriStr .= '/';
            }

            $uriStr .= $path;
        }



        if ($uri->getQuery() !== '') {
            $uriStr .= '?' . $uri->getQuery();
        }

        if ($uri->getFragment() !== '') {
            $uriStr .= '#' . $uri->getFragment();
        }

        return $uriStr;
    }
}