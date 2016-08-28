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

use Core\Contracts\Reactor\Validator;
use Core\Application\Console\Validators\OptionsValidator;


/**
 * Class Argument
 * @package Core\Console
 */
class Argument {

    /**
     * @var string $name
     */
    protected $name;
    /**
     * @var string $description
     */
    protected $description;
    /**
     * @var null $default
     */
    public $default = null;
    /**
     * @var bool $required
     */
    protected $required = false;

    protected $validations = [];


    /**
     * Argument constructor
     *
     * @param string $name
     * @param bool $required
     * @param null|string $description
     * @param null|mixed $default
     */
    public function __construct($name, $description = null, $default = null, $required = false)
    {
        $this->name = $name;
        $this->description = $description;
        $this->default = $default;
        $this->required = $required;
    }


    /**
     * @return string
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setDefault($default = null)
    {
        $this->default = $default;
    }

    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return !$this->required;
    }

    /**
     * @param mixed $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * @param Validator $validator
     */
    public function addValidation(Validator $validator)
    {
        $this->validations[] = $validator;
    }

    /**
     * @param string|array $validator
     */
    public function mustValidate($validator)
    {
        if (is_string($validator)) {
            $obj = new $validator();
            $this->addValidation($obj);
        } elseif ($validator instanceOf Validator) {
            $this->addValidation($validator);
        }
    }

    /**
     * @return bool
     */
    public function hasValidation()
    {
        return !empty($this->validations);
    }

    public function validate($subject)
    {
        if (!empty($this->validations)) {
            foreach ($this->validations as $i => $validator) {
                $validator->validate($subject);
            }
        }

        return $subject;
    }
}