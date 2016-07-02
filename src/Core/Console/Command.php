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

namespace Core\Console;


class Command {

    const OPTIONAL = "::";
    const REQUIRED = ":";
    protected $name;
    protected $options = [];
    protected $arguments = [];
    protected $description;
    protected $definition;
    private $_map;


    function __construct($name, $description, $definition)
    {
        if (is_string($name) === false) {
            throw new \ErrorException("Parameter name must be a string");
        }
        if (is_string($description) === false) {
            throw new \ErrorException("Parameter description must be string");
        }
        if ($definition instanceof \Closure === false && !is_string($definition)) {
            throw new \ErrorException("Parameter definition must be a Closure or string");
        }

        $this->name = $name;
        $this->description = $description;
        $this->definition = $definition;

        return $this;
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
    public function hasOptions()
    {
        return sizeof($this->options) > 0 ? true : false;
    }

    /**
     * @return mixed
     */
    public function getOptions($name = null)
    {
        if(is_null($name)){
            return $this->options;
        }
        return $this->options[$name];
    }

    /**
     * @return array|bool
     */
    public function getOptionsAsArray()
    {
        $longOpts = [];
        $shortOpts = "";

        if (!empty($this->options)) {
            foreach($this->options as $key => $val) {
                $sym =  $this->options[$key]->getIsRequired();
                $shortname = $this->options[$key]->getShortname();
                array_push($longOpts, $key . $sym);
                $shortOpts .= $shortname . $sym;
            }

        }

        return [$shortOpts, $longOpts];
    }

    /**
     * @param $name
     * @param null $shortName
     * @param null $description
     * @param null $optionVal
     * @param null $isRequired
     * @return $this
     * @throws \ErrorException
     */
    public function setOptions($name, $shortName = null, $description = null, $optionVal = null, $isRequired = null)
    {
        if (!empty($shortName)) {
            $this->_map[$shortName] = $name;
        }
        if (empty($name)) {
            throw new \ErrorException("\$name cannot be null");
        }
        $this->options[$name] = new Options($name, $shortName, $description, $optionVal, $isRequired);
        return $this;
    }

    /**
     * @param $shortName
     * @return mixed
     */
    public function getNameFromShortName($shortName)
    {
        return $this->_map[$shortName];
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
     * @return mixed
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param $definition
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param $name
     * @param bool $required
     * @param null $description
     * @return $this
     */
    public function addArguments($name, $required = true, $description = null)
    {
        $this->arguments[] = new Argument($name, $required, $description);

        return $this;
    }
}