<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 17/01/16
 * Time: 2:34 AM
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