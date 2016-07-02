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

namespace Core\Database;


class ForeignKey
{
    const RESTRICT = 'RESTRICT';
    const CASCADE = 'CASCADE';
    const SET_NULL = 'SET NULL';
    const NO_ACTION = 'NO ACTION';

    /**
     * @var string $constraint
     */
    protected $constraint;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var Table $referenceTable
     */
    protected $referenceTable;

    /**
     * @var array $referenceColumns
     */
    protected $referenceColumns = [];

    /**
     * @var string $onDelete
     */
    protected $onDelete;

    /**
     * @var string $opUpdate
     */
    protected $onUpdate;

    /**
     * @return string
     */
    public function getConstraint()
    {
        return $this->constraint;
    }

    /**
     * @param $constraint
     * @return $this
     */
    public function setConstraint($constraint)
    {
        $this->constraint = $constraint;
        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param $columns
     * @return $this
     */
    public function setColumns($columns)
    {
        if (is_string($columns)) {
            $columns = array($columns);
        }
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return mixed|Table
     */
    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    /**
     * @param $referenceTable
     * @return $this
     */
    public function setReferenceTable($referenceTable)
    {
        $this->referenceTable = $referenceTable;
        return $this;
    }

    /**
     * @return array
     */
    public function getReferenceColumns()
    {
        return $this->referenceColumns;
    }

    /**
     * @param $referenceColumns
     * @return $this
     */
    public function setReferenceColumns($referenceColumns)
    {
        $this->referenceColumns = $referenceColumns;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOnDelete()
    {
        return $this->onDelete;
    }

    /**
     * @param $onDelete
     * @return $this
     */
    public function setOnDelete($onDelete)
    {
        $realOption = $this->getSqlReferenceOption($onDelete);
        $this->onDelete = $realOption;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOnUpdate()
    {
        return $this->onUpdate;
    }

    /**
     * @param $onUpdate
     * @return $this
     */
    public function setOnUpdate($onUpdate)
    {
        $realOption = $this->getSqlReferenceOption($onUpdate);
        $this->onUpdate = $realOption;
        return $this;
    }

    /**
     * @param $option
     * @return mixed
     */
    protected function getSqlReferenceOption($option)
    {
        $realOption = 'static::' . strtoupper($option);
        if (!defined($realOption)) {
            throw new \InvalidArgumentException("Unknown reference option: {$option} provided.");
        }

        return constant($realOption);
    }

    /**
     * @param $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        // Valid Options
        $validOptions = array('onDelete', 'onUpdate', 'constraint');
        foreach ($options as $option => $value) {
            if (!in_array($option, $validOptions)) {
                throw new \RuntimeException("'{$option}' is not a valid Foreign Key option.");
            }

            $method = 'set' . ucfirst($option);
            $this->$method($value);
        }

        return $this;
    }
}