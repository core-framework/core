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

namespace Core\Application\Console;

class Option
{
    CONST OPTION_LONG = "--";
    CONST OPTION_SHORT = "-";
    CONST OPTION_OPTIONAL = 0;
    CONST OPTION_REQUIRED = 1;
    CONST OPTION_FLAG = 2;
    protected $name;
    protected $shortName;
    protected $description;
    protected $type = false;
    protected $default = true;

    /**
     * @param $name
     * @param null $shortName
     * @param null $description
     * @param int $type Permitted values are OPTION_OPTIONAL|OPTION_REQUIRED|OPTION_FLAG
     * @throws \ErrorException
     */
    function __construct($name, $shortName = null, $description = null, $type = self::OPTION_OPTIONAL)
    {
        $this->name = $name;
        $this->shortName = $shortName;
        $this->description = $description;
        $this->type = $type;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasShortName()
    {
        return isset($this->shortName);
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param $shortName
     * @return $this
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->type === self::OPTION_REQUIRED;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->type === self::OPTION_OPTIONAL;
    }

    /**
     * @return bool
     */
    public function isFlag()
    {
        return $this->type === self::OPTION_FLAG;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        if (!is_int($type)) {
            throw new \InvalidArgumentException("Invalid argument type:{$type}. Expected Option::OPTION_OPTIONAL || Option::OPTION_REQUIRED || Option::OPTION_FLAG");
        }
        $this->type = $type;
    }

    /**
     * @param $value
     */
    public function setDefault($value)
    {
        $this->default = $value;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }
}