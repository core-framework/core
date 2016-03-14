<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 24/01/16
 * Time: 3:47 PM
 */

namespace Core\Database;


class Where
{
    protected $column;

    protected $symbol = '=';

    protected $value;

    /**
     * Where constructor.
     * @param $column
     * @param string $symbol
     * @param $value
     */
    public function __construct($column, $value, $symbol = '=')
    {
        $this->column = $column;
        $this->symbol = $symbol;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param $column
     * @return Where
     */
    public function setColumn($column)
    {
        $this->column = $column;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param $symbol
     * @return Where
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return Where
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Return sql (without the WHERE clause)
     *
     * @return string
     */
    public function getSql()
    {
        $quotedValue = '';
        if (is_numeric($this->value)) {
            $quotedValue = (int) $this->value;
        } else {
            $quotedValue = "'{$this->value}'";
        }
        $sql = " `{$this->column}` {$this->symbol} {$quotedValue}";
        return $sql;
    }
}