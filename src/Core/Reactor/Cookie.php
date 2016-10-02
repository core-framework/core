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


namespace Core\Reactor;


class Cookie
{
    protected $name;
    protected $value;
    protected $domain;
    protected $expires;
    protected $path;
    protected $secure;
    protected $httpOnly;

    /**
     * Cookie constructor.
     * @param $name
     * @param $value
     * @param $domain
     * @param $expires
     * @param $path
     * @param $secure
     * @param $httpOnly
     */
    public function __construct($name, $value = null, $domain = null, $expires = 0, $path = '/', $secure = false, $httpOnly = true)
    {
        if (preg_match("/[~,;`= \t\r\n]/", $name)) {
            throw new \InvalidArgumentException("The cookie name '{$name}' contains invalid characters");
        }
        if (!is_null($value) && preg_match("/[~,;`= \t\r\n]/", $value)) {
            throw new \InvalidArgumentException("The cookie value '{$value}' contains invalid characters");
        }

        if (!is_numeric($expires)) {
            $expires = strtotime($expires);
        } elseif ($expires instanceof \DateTime) {
            $expires = $expires->format('U');
        }

        if ($expires === -1 || $expires === false) {
            throw new \InvalidArgumentException("Invalid expires value given: {$expires}");
        }

        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expires = $expires;
        $this->path = $path;
        $this->secure = (bool) $secure;
        $this->httpOnly = (bool) $httpOnly;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $str = urlencode($this->getName()) . '=';

        if ((string) $this->getValue() === '') {
            $str .= 'deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT;';
        } else {
            $str .= urlencode($this->getValue()) . "; ";

            if ($this->getExpires() !== 0) {
                $str .= 'expires='.gmdate('D, d-M-Y H:i:s T', $this->getExpires()) . '; ';
            }
        }

        if ($this->getPath()) {
            $str .= 'path='.$this->getPath().'; ';
        }

        if ($this->getDomain()) {
            $str .= 'domain='.$this->getDomain().'; ';
        }

        if ($this->getSecure()) {
            $str .= 'secure; ';
        }

        if ($this->getHttpOnly()) {
            $str .= 'httponly;';
        }

        return $str;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param mixed $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @param mixed $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return mixed
     */
    public function getHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * @param mixed $httpOnly
     */
    public function setHttpOnly($httpOnly)
    {
        $this->httpOnly = $httpOnly;
    }

    /**
     * if Cookie has expired
     *
     * @return bool
     */
    public function hasExpired()
    {
        return $this->expires < time();
    }
}