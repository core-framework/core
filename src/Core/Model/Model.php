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

namespace Core\Model;

use Core\Contracts\Database\Mapper;
use Core\Contracts\ModelContract;
use Core\Database\QueryBuilder;
use Core\Database\Table;
use Core\Database\Where;

/**
 * This is the base model class for Core Framework
 *
 * @package Core\Models
 * @version $Revision$
 * @license http://creativecommons.org/licenses/by-sa/4.0/
 * @link http://coreframework.in
 * @author Shalom Sam <shalom.s@coreframework.in>
 */
class Model extends BaseModel implements ModelContract
{
    /**
     * @var string Database table name
     */
    protected static $tableName = '';
    /**
     * @var string Table primary key
     */
    protected static $primaryKey = '';
    /**
     * @var string Database name
     */
    protected static $dbName = '';
    /**
     * @var bool Flag to filter dates (on save)
     */
    protected static $unsetDates = true;
    /**
     * @var bool Determines whether to drop rows permanently or preserve
     */
    protected static $useSoftDelete = false;
    /**
     * @var Table $tableSchema Table Schema
     */
    protected static $tableSchema;


    /**
     * @param array|null $data
     * @param Mapper|null $mapper
     */
    public function __construct(array $data = null, Mapper $mapper = null)
    {
        if (!empty($data)) {
            self::configure($this, $data);
        }
        parent::__construct($mapper);
    }

    /**
     * Unset un-used parameters before storing in Database
     */
    protected function beforeSave()
    {
        if (!empty(static::$saveable))  {
            $objArr = (array) $this;
            foreach($objArr as $column => $value) {
                if (!in_array($column, static::$saveable)) {
                    unset($this->$column);
                }
            }
        }

        if (self::$unsetDates === true) {
            unset($this->{Mapper::CREATED_AT});
            unset($this->{Mapper::MODIFIED_AT});
            unset($this->{Mapper::DELETED_AT});
        }
    }

    protected function beforeDelete()
    {

    }

    /**
     * @param $tableName
     *
     * @return Table
     */
    public function getTableSchema($tableName = null)
    {
        if (!isset(static::$tableSchema)) {
            $columns = static::$saveable;
            if (is_null($tableName)) {
                $tableName = static::getTableName();
            }
            $table = new Table($tableName, ['primaryKey' => static::getPrimaryKey()]);
            foreach($columns as $column) {
                $table->addColumn($column);
            }
            static::setTableSchema($table);
        }

        return static::$tableSchema;
    }

    /**
     * @param Table $tableSchema
     * @return Model
     */
    public static function setTableSchema(Table $tableSchema)
    {
        static::$tableSchema = $tableSchema;
    }

    /**
     * Updates the database with the properties set
     */
    public function save()
    {
        $this->beforeSave();
        $table = $this->getTableSchema();
        try {
            return $this->getMapper()->insert($table, [$this->toArray()]);
        } catch (\PDOException $e) {
            throw new \ErrorException($e->getMessage(), $e->getCode(), 1, $e->getFile(), $e->getLine());
        }
    }

    public function update()
    {
        $primaryKey = $this->getPrimaryKey();
        $where = new Where($primaryKey, $this->{$primaryKey});
        $this->beforeSave();
        return $this->getMapper()->update($this->getTableName(), $this->toArray(), [$where]);
    }

    /**
     * Deletes row from the database table
     */
    public function delete()
    {
        $primaryKey = $this->getPrimaryKey();
        $where = new Where($primaryKey, $this->$primaryKey);
        $this->beforeDelete();
        return $this->getMapper()->dropRows($this->getTableName(), [$where]);
    }

    /**
     * Marks row as deleted (to be used with deleted_at column in table)
     */
    public function softDelete()
    {
        if (!static::$useSoftDelete) {
            throw new \LogicException('Soft Delete must be explicitly set to true before you can use it.');
        }
        $this->beforeDelete();
        $primaryKey = $this->getPrimaryKey();
        $where = new Where($primaryKey, $this->{$primaryKey});
        return $this->getMapper()->update($this->getTableName(), ['deleted_at' => date('Y-m-d H:i:s')], [$where]);
    }

    /**
     * @param null $tableName
     * @param bool|true $delete
     * @return bool
     */
    public function move($tableName = null, $delete = true)
    {
        $this->getMapper()->beginTransaction();
        if (is_null($tableName)) {
            $tableName = $this->getTableName() . '_deleted';
        }
        $schema = $this->getTableSchema($tableName);
        try {
            $this->getMapper()->insert($schema, [$this->toArray()]);
            if ($delete === true) {
                $this->delete();
            }
            $this->getMapper()->commit();
        } catch (\PDOException $e) {
            $this->getMapper()->rollback();
        }

        return true;
    }

    /**
     * @param $datetime
     * @param bool|false $full
     * @return string
     */
    public function time_elapsed_string($datetime, $full = false)
    {
        $now = new \DateTime();
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = (array) $this;

        foreach ($array as $column => $value) {
            if (!in_array($column, static::$fillable)) {
                unset($array[$column]);
            }
        }

        return $array;
    }


    /**
     * @param $class
     * @param null $foreignKey
     * @param null $localKey
     * @return mixed
     */
    public function hasOne($class, $foreignKey = null, $localKey = null)
    {
        $classObj = new $class();
        $tableName = $classObj->getTableName();
        $condition = $this->getRelationCondition($this, $foreignKey, $localKey);
        $statement = $this->getStatement($tableName, $condition);
        return $statement->fetchObject($class);
    }

    public function hasMany($class, $foreignKey = null, $localKey = null)
    {
        $classObj = new $class();
        $tableName = $classObj->getTableName();
        $condition = $this->getRelationCondition($this, $foreignKey, $localKey);
        if (static::$returnRelationsAsBuilder) {
            return QueryBuilder::make($this->getMapper(), $tableName, [], $condition)->setModel($class);
        } else {
            $statement = $this->getStatement($tableName, $condition);
            return $statement->fetchObject($class);
        }
    }

    public function belongsTo($class, $foreignKey = null, $parentKey = null)
    {
        $classObj = new $class();
        $tableName = $classObj->getTableName();
        $id = is_null($parentKey) ? 'id' : $parentKey;

        if (is_null($foreignKey)) {
            $reflection = new \ReflectionClass($classObj);
            $className = $reflection->getShortName();
            $foreignKey = strtolower($className) . '_' . $id;
            $foreignKeyVal = $this->{$foreignKey};
        } else {
            $foreignKeyVal = $this->{$foreignKey};
        }
        $condition = [new Where($id, $foreignKeyVal)];
        $statement = $this->getStatement($tableName, $condition);
        return $statement->fetchObject($class);
    }

    public function belongsToMany($class, $joinTable = null, $localForeignKey = null, $siblingForeignKey = null)
    {
        $siblingClassObj = new $class();
        $siblingTableName = $siblingClassObj->getTableName();
        $siblingPrimaryKey = $siblingClassObj->getPrimaryKey();

        if (is_null($joinTable)) {
            $parentTableName = $this->getTableName();
            if (strcmp($parentTableName, $siblingTableName) > 0) {
                $joinTable = $siblingTableName . '_' . $parentTableName;
            } elseif (strcmp($siblingTableName, $parentTableName) > 0) {
                $joinTable = $parentTableName . '_' . $siblingTableName;
            } else {
                throw new \LogicException('Unable to determine Join table name.');
            }
        }

        if (is_null($localForeignKey)) {
            $localForeignKey = $this->getForeignKey();
        }

        if (is_null($siblingForeignKey)) {
            $siblingForeignKey = $this->getForeignKey($siblingClassObj);
        }

        $primaryKey = $this->getPrimaryKey();
        $primaryKeyVal = $this->{$primaryKey};

        $condition = [new Where($localForeignKey, $primaryKeyVal)];
        $statement = $this->getMapper()->getAll($joinTable, [$siblingForeignKey], $condition);
        $siblingKeyValues = $statement->fetchAll(\PDO::FETCH_NUM);

        $conditions = [];
        foreach($siblingKeyValues as $index => $value) {
            $conditions[] = new Where($siblingPrimaryKey, $value[0]);
        }

        if (static::$returnRelationsAsBuilder) {
            return QueryBuilder::make($this->getMapper(), $siblingTableName, [], $conditions)->setModel($class);
        } else {
            $statement2 = $this->getStatement($siblingTableName, $conditions);
            return $statement2->fetchObject($class);
        }
    }

    public function getForeignKey($obj = null)
    {
        $obj = is_null($obj) ? $this : $obj;
        if (!$obj instanceof ModelContract) {
            throw new \LogicException('Cannot get foreign Key for a non-Model object!');
        }
        $className = new \ReflectionClass($obj);
        $className = $className->getShortName();
        return strtolower($className) . '_' . $obj->getPrimaryKey();
    }

    private function getRelationCondition($classObj, $foreignKey = null, $localKey = null)
    {
        /** @var ModelContract $classObj */

        if (is_null($foreignKey)) {
            $reflection = new \ReflectionClass($classObj);
            $className = $reflection->getShortName();
            $foreignKey = strtolower($className) . '_id';
        }

        if (!is_null($localKey)) {
            $primaryKeyVal = $classObj->{$localKey};
        } else {
            $primaryKey = $classObj->getPrimaryKey();
            $primaryKeyVal = $classObj->{$primaryKey};
        }

        return [new Where($foreignKey, $primaryKeyVal)];
    }


    private function getStatement($tableName, array $conditions = [])
    {
        $statement = $this->getMapper()->getAll($tableName, [], $conditions);
        if ($statement === false) {
            throw new \PDOException("SQL Error: {$this->getConnection()->errorCode()} : {$this->getConnection()->errorInfo()[2]}");
        }
        return $statement;
    }

}