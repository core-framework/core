<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 09/01/16
 * Time: 6:18 PM
 */

namespace Core\Database;


class Column
{
    /**
     * Contains the name of the selected column.
     *
     * @var string
     */
    protected $name;

    /**
     * Contains the data type for the selected column.
     *
     * @var string
     */
    protected $dataType;

    /**
     * Contains the size of the data Type.
     *
     * @var int
     */
    protected $size;

    /**
     * Indicates whether this column allows nulls.
     *
     * @var bool
     */
    protected $null = true;

    /**
     * Contains the default for this column whenever no value is specified for this column.
     *
     * @var null
     */
    protected $default = null;

    /**
     * Indicates whether this column has Auto Increment (Identity).
     *
     * @var bool
     */
    protected $autoIncrement = false;

    /**
     * Indicates if Auto Increment is true, what the start value should be.
     *
     * @var int
     */
    protected $incrementStart;

    /**
     * Contains the maximum number of digits that can appear to the right of the decimal point for values of this column.
     *
     * @var int
     */
    protected $scale;

    /**
     * Contains the maximum number of digits allowed (including digits after decimal) for values in this column.
     *
     * @var int
     */
    protected $precision = 0;

    /**
     * Indicates if column uses signed dataType.
     *
     * @var bool
     */
    protected $signed = false;

    /**
     * Contains Column Values
     *
     * @var array
     */
    protected $values;

    /**
     * Contains the Column name for AFTER clause
     *
     * @var string $after
     */
    protected $after;

    /**
     * Contains Sql function/constant to execute on Update of this column
     *
     * @var string $update
     */
    protected $update;

    /**
     * Contains the format for Date & Time Data Types
     *
     * @var string $format
     */
    protected $format;

    /**
     * @var bool
     */
    protected $primaryKey = false;

    /**
     * @var bool
     */
    protected $foreignKey = false;

    /**
     * Contains the list of valid options
     *
     * @var array
     */
    protected $validOptions = [
        'size',
        'primaryKey',
        'null',
        'default',
        'autoIncrement',
        'incrementStart',
        'incrementBy',
        'scale',
        'precision',
        'signed',
        'after',
        'update'
    ];


    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach($options as $option => $value) {
            if (!in_array($option, $this->validOptions)) {
                throw new \InvalidArgumentException("{$option} is not a valid option.");
            }

            $method = 'set' . ucfirst($option);
            $this->$method($value);
        }
    }

    /**
     * @return string
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
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param $dataType
     * @return $this
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNull()
    {
        return $this->null;
    }

    /**
     * @param $null
     * @return $this
     */
    public function setNull($null)
    {
        $this->null = $null;

        return $this;
    }

    /**
     * @return null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;
        //Logically
        $this->setNull(false);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAutoIncrement()
    {
        return $this->autoIncrement;
    }

    /**
     * @param $autoIncrement
     * @return $this
     */
    public function setAutoIncrement($autoIncrement)
    {
        $this->autoIncrement = (bool) $autoIncrement;

        return $this;
    }

    /**
     * @return int
     */
    public function getIncrementStart()
    {
        return $this->incrementStart;
    }

    /**
     * @param $incrementStart
     * @return $this
     */
    public function setIncrementStart($incrementStart)
    {
        $this->incrementStart = $incrementStart;

        return $this;
    }

    /**
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @param $scale
     * @return $this
     */
    public function setScale($scale)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @param $precision
     * @return $this
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSigned()
    {
        return $this->signed;
    }

    /**
     * @param $signed
     * @return $this
     */
    public function setSigned($signed)
    {
        $this->signed = $signed;

        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param $values
     * @return $this
     */
    public function setValues($values)
    {
        if (!is_array($values)) {
            $values = preg_split('/,\s*/', $values);
        }
        $this->values = $values;
        return $this;
    }

    /**
     * @return string
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * @param $after
     * @return $this
     */
    public function setAfter($after)
    {
        $this->after = $after;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @param $update
     * @return $this
     */
    public function setUpdate($update)
    {
        $this->update = $update;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param $primaryKey
     * @return $this
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = (bool) $primaryKey;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param $foreignKey
     * @return $this
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = (bool) $foreignKey;

        return $this;
    }
}