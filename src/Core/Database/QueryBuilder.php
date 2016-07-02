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

use Core\Contracts\Database\Mapper;
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
     * @param Mapper $language
     */
    public function __construct(Mapper $language)
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
     * @return Mapper
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
     * @param Mapper $language
     * @param $tableName
     * @param array $column
     * @param array $conditions
     * @param array $orderBy
     * @param null $groupBy
     * @return QueryBuilder
     */
    public static function make(Mapper $language, $tableName, $column = [], $conditions = [], $orderBy = [], $groupBy = null)
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