<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 08/01/16
 * Time: 3:50 PM
 */

namespace Core\Database;

use Core\Contracts\Database\LanguageContract;
use Core\Contracts\Database\QueryBuilderContract;

class QueryBuilder implements QueryBuilderContract
{
    protected $query;
    protected $tableName;
    protected $columns = [];
    protected $conditions = [];
    protected $groupBy;
    protected $orderBy = [];
    protected $language;
    protected $model;
    protected $collection = [];

    /**
     * QueryBuilder constructor.
     * @param LanguageContract $language
     */
    public function __construct(LanguageContract $language)
    {
        $this->setLanguage($language);
    }

    /**
     * @param bool|false|int $index
     * @return array
     */
    public function get($index = false)
    {
        $statement = $this->getLanguage()->getAll($this->tableName, $this->columns, $this->conditions, $this->orderBy, $this->groupBy);
        if ($statement === false) {
            throw new \PDOException("SQL Error: {$this->getLanguage()->getConnection()->errorCode()} : {$this->getLanguage()->getConnection()->errorInfo()[2]}");
        }
        if (isset($this->model)) {
            $this->collection = $statement->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $this->getModel());
        } else {
            $this->collection = $statement->fetchAll(\PDO::FETCH_ASSOC);
        }
        if (!is_int($index)) {
            return $this->collection;
        }
        return $this->collection[$index];
    }

    /**
     * @param $column
     * @param $value
     * @param string $equator
     * @return $this
     */
    public function where($column, $value, $equator = '=')
    {
        $this->conditions[] = new Where($column, $value, $equator);
        return $this;
    }

    /**
     * @param $column
     * @return $this
     */
    public function groupBy($column)
    {
        $this->groupBy = $column;
        return $this;
    }

    /**
     * @param $conditions
     * @param string $orderType
     * @return $this
     */
    public function orderBy($conditions, $orderType = 'DESC')
    {
        if (is_array($conditions)) {
            $this->orderBy = $conditions;
        } else {
            $this->orderBy[] = [$conditions => $orderType];
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param mixed $query
     * @return QueryBuilder
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     * @return QueryBuilder
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     * @return QueryBuilder
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @return LanguageContract
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     * @return QueryBuilder
     */
    public function setLanguage($language)
    {
        $this->language = $language;
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
     * @param array $columns
     * @return QueryBuilder
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @param $column
     * @return QueryBuilder
     */
    public function addColumn($column)
    {
        $this->columns[] = $column;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     * @return QueryBuilder
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param LanguageContract $language
     * @param $tableName
     * @param array $column
     * @param array $conditions
     * @param array $orderBy
     * @param null $groupBy
     * @return QueryBuilder
     */
    public static function make(LanguageContract $language, $tableName, $column = [], $conditions = [], $orderBy = [], $groupBy = null)
    {
        $builder = new self($language);
        $builder->setTableName($tableName)
            ->setColumns($column)
            ->setConditions($conditions)
            ->orderBy($orderBy)
            ->groupBy($groupBy);

        return $builder;
    }
}