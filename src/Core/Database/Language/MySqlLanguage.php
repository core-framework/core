<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 13/01/16
 * Time: 5:53 PM
 */

namespace Core\Database\Language;


use Core\Contracts\Database\LanguageContract;
use Core\Database\Column;
use Core\Database\Connection;
use Core\Database\ForeignKey;
use Core\Database\Table;
use Core\Database\Where;

class MySqlLanguage extends BaseLanguage implements LanguageContract
{
    protected $signedDataTypes = [
        'integer' => true,
        'int' => true,
        'tinyint' => true,
        'tinyinteger' => true,
        'smallint' => true,
        'smallinteger' => true,
        'biginteger' => true,
        'bigint' => true,
        'real' => true,
        'double' => true,
        'float' => true,
        'decimal' => true,
        'numeric' => true
    ];

    protected static $defaultFloat = [
        'precision' => 10,
        'scale' => 2
    ];

    protected static $defaultDouble = [
        'precision' => 16,
        'scale' => 4
    ];

    protected $saveableColumns = [];

    protected $params = [];

    // Numeric Types
    const DEFAULT_INT = 11;
    const DEFAULT_TINYINT = 4;
    const DEFAULT_SMALLINT = 5;
    const DEFAULT_MEDIUMINT = 9;
    const DEFAULT_BIGINT = 20;
    const DEFAULT_FLOAT_PRECISION = 10;
    const DEFAULT_FLOAT_SCALE = 2;
    const DEFAULT_DOUBLE_PRECISION = 16;
    const DEFAULT_DOUBLE_SCALE = 4;
    const DEFAULT_DECIMAL_PRECISION = 18;
    const DEFAULT_DECIMAL_SCALE = 6;

    // Date and Time types
    const DEFAULT_DATE = "YYYY-MM-DD";
    const DEFAULT_DATETIME = "YYYY-MM-DD HH:MM:SS";
    const DEFAULT_TIMESTAMP = "YYYYMMDDHHMMSS";
    const DEFAULT_TIME = "HH:MM:SS";
    const DEFAULT_YEAR = 4;

    // String Types
    const DEFAULT_CHAR = 1;
    const DEFAULT_VARCHAR = 255;
    const MAX_TINYBLOB = 255;
    const MAX_TINYTEXT = 255;
    const MAX_MEDIUMBLOB = 16777215;
    const MAX_MEDIUMTEXT = 16777215;
    const MAX_LONGBLOB = 4294967295;
    const MAX_LONGTEXT = 4294967295;


    /**
     * @return Connection
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * @param Connection $connection
     * @return $this
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Connect to mysql server
     */
    protected function connect()
    {
        if ($this->connection !== null)
            return;

        $config = $this->getConfig();

        $pdoDriver = isset($config['$db']['type']) ? $config['$db']['type'] : 'mysql';

        if (!class_exists('PDO') || !in_array($pdoDriver, \PDO::getAvailableDrivers(), true)) {
            throw new \RuntimeException("You need to enable/install the PDO_{$pdoDriver} extension.");
        }

        $config = array_merge($config, array('options' => array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_EMULATE_PREPARES => false)));

        $connection = new Connection($config);

        $this->setConnection($connection);
    }

    /**
     * SQL begin transaction
     */
    public function beginTransaction()
    {
        $this->getConnection()->beginTransaction();
    }

    /**
     * SQL commit Transaction
     */
    public function commit()
    {
        $this->getConnection()->commit();
    }

    /**
     * SQL rollback transaction
     */
    public function rollback()
    {
        $this->getConnection()->rollBack();
    }

    /**
     * @param $string
     * @return string
     */
    public function quote($string)
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException("'quote' function expect 1 parameter to be a string. " . gettype($string) . " given.");
        }
        return "`".str_replace("`","``",$string)."`";
    }

    /**
     * @param array $arr
     * @return array
     */
    public function arrayQuote(array $arr)
    {
        foreach ($arr as $index => $string) {
            $arr[$index] = $this->quote($string);
        }

        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($sql, $params = [])
    {
        if (!empty($params)) {
            $prepared = $this->getPrepared($sql);
            $result = $prepared->execute($params);
            if ($result === false) {
                throw new \PDOException("SQL Error: {$this->getConnection()->errorCode()} : {$this->getConnection()->errorInfo()[2]}");
            }
            return $prepared->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $result = $this->query($sql);
            if ($result === false) {
                throw new \PDOException("SQL Error: {$this->getConnection()->errorCode()} : {$this->getConnection()->errorInfo()[2]}");
            }
            return $result->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * @param $sql
     * @return int
     */
    public function exec($sql)
    {
        return $this->getConnection()->exec($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrepared($sql)
    {
        return $this->getConnection()->getPrepared($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function query($sql)
    {
        return $this->getConnection()->query($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Table $table, $data)
    {
        $connection = $this->getConnection();

        $columns = $this->getColumnNamesArr($table);
        $columnStr = implode(",", $columns);

        $build = $this->buildValuesStr($data);
        $valuesStr = implode(",", $build['rowPlaceholders']);

        $tableName = $table->getName();

        $sql = "INSERT INTO {$tableName} ({$columnStr}) VALUES {$valuesStr}";
        $prepared = $connection->getPrepared($sql);
        $result = $prepared->execute($build['values']);

        return $result;
    }

    /**
     * @param Table $table
     * @return array
     */
    public function getColumnNamesArr(Table $table)
    {
        /** @var Column[] $columnsObjArr */
        $columnsObjArr = $table->getColumns();
        $columns = [];
        foreach($columnsObjArr as $obj)
        {
            if ($this->isColumnSaveable($obj->getName())) {
                $columns[] = $obj->getName();
            }
        }
        return $columns;
    }

    /**
     * @param $column
     * @return bool
     */
    private function isColumnSaveable($column)
    {
        $saveableColumns = $this->getSaveableColumns();
        return (!empty($saveableColumns) && in_array($column, $saveableColumns)) || ($column !== static::CREATED_AT && $column !== static::MODIFIED_AT && $column !== static::DELETED_AT);
    }

    /**
     * @param array $data
     * @return array
     */
    public function buildValuesStr(array $data)
    {
        $insert_values = [];
        $question_marks = [];
        foreach($data as $index => $row) {
            $question_marks[] = $this->placeholders('?', sizeof($row));
            $insert_values = array_merge($insert_values, array_values($row));
        }

        return ['rowPlaceholders' => $question_marks, 'values' => $insert_values];
    }

    /**
     * Creates placeholders for prepare statement
     *
     * @param $text
     * @param int $count
     * @param string $separator
     * @return string
     */
    public function placeholders($text, $count = 0, $separator = ",")
    {
        $result = [];
        if($count > 0){
            for($x=0; $x<$count; $x++){
                $result[] = $text;
            }
        }

        return '(' .implode($separator, $result) . ')';
    }

    /**
     * {@inheritdoc}
     */
    public function truncate($tableName)
    {
        $this->getConnection()->exec("TRUNCATE {$tableName}");
    }

    /**
     * {@inheritdoc}
     */
    public function create(Table $table)
    {
        $columns = $table->getColumns();
        $tableOptions = $table->getOptions();

        if (isset($tableOptions['id']) && $tableOptions['id'] === true) {
            $column = new Column();
            $column->setName('id')
                   ->setDataType('integer')
                   ->setAutoIncrement(true);

            array_unshift($columns, $column);
            $tableOptions['primaryKey'] = 'id';

        } elseif (isset($tableOptions['id']) && is_string($tableOptions['id'])) {
            $column = new Column();
            $column->setName($tableOptions['id'])
                   ->setDataType('integer')
                   ->setAutoIncrement(true);

            array_unshift($columns, $column);
            $tableOptions['primaryKey'] = $tableOptions['id'];

        }

        $sql = "CREATE TABLE {$this->quote($table->getName())} (";

        /** @var Column $column */
        foreach($columns as $column) {
            $sql .= "{$this->quote($column->getName())} {$this->getColumnDefinition($column)},";
        }

        // Primary Key Assignment
        if (isset($tableOptions['primaryKey'])) {

            if (is_array($tableOptions['primaryKey'])) {
                $quotedCols = implode(",", $this->arrayQuote($tableOptions['primaryKey']));
                $sql .= " PRIMARY KEY ({$quotedCols})";
            } elseif (is_string($tableOptions['primaryKey'])) {
                $sql .= " PRIMARY KEY ({$this->quote($tableOptions['primaryKey'])})";
            }

        } else {
            $primaryKeysArr = $table->getPrimaryKeyNames();
            if (!empty($primaryKeysArr)) {
                $keys = implode(",", $this->arrayQuote($primaryKeysArr));
                $sql .= " PRIMARY KEY ({$keys})";
            }
        }

        // TODO: add handling for indexes

        // Foreign Key Assignment
        $foreignKeys = $table->getForeignKeys();
        if (!empty($foreignKeys)) {
            foreach($foreignKeys as $foreignKey) {
                $sql .= ", {$this->getForeignKeyDefinition($foreignKey)}";
            }
        }

        $sql = rtrim($sql, ',');
        $sql .= ") {$table->getTableOptionsStr()};";
        $result = $this->getConnection()->exec($sql);

        if ($result !== false) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param ForeignKey $foreignKey
     * @return string
     */
    protected function getForeignKeyDefinition(ForeignKey $foreignKey)
    {
        $sql = "";
        if ($constraint = $foreignKey->getConstraint() !== null) {
            $sql .= "CONSTRAINT {$this->quote($constraint)}";
        }

        $columnNames = [];
        $columns = $foreignKey->getColumns();
        foreach($columns as $column) {
            $columnNames[] = $this->quote($column);
        }
        $sql .= ' FOREIGN KEY (' .implode(",", $columnNames) . ')';

        $refColumnNames = [];
        $refColumns = $foreignKey->getReferenceColumns();
        foreach($refColumns as $column) {
            $refColumnNames[] = $this->quote($column);
        }
        $columnNamesStr = implode(',', $refColumnNames);
        $sql .= " REFERENCES {$this->quote($foreignKey->getReferenceTable()->getName())}({$columnNamesStr})";

        if ($foreignKey->getOnUpdate()) {
            $sql .= " ON UPDATE {$foreignKey->getOnUpdate()}";
        }
        if ($foreignKey->getOnDelete()) {
            $sql .= " ON DELETE {$foreignKey->getOnDelete()}";
        }

        return $sql;
    }

    /**
     * Returns the SQL string containing all column specifications
     *
     * @param Column $column
     * @return string
     */
    protected function getColumnDefinition(Column $column)
    {
        $sql = "";
        $size = $column->getSize();
        $realDataType = $this->getRealDataType($column->getDataType(), $size);

        $sql .= strtoupper($realDataType['dataType']);
        $precision = $column->getPrecision();
        $scale = $column->getScale();

        if ($scale && $precision) {
            $sql .= "({$precision}, {$scale})";
        } elseif (isset($realDataType['size']) && is_array($realDataType['size'])) {
            $sql .= "({$realDataType['size']['precision']}, {$realDataType['size']['scale']})";
        } elseif (isset($realDataType['size'])) {
            $sql .= "({$realDataType['size']})";
        }

        if ($column->isSigned() && $this->signedDataTypes[$realDataType['dataType']]) {
            $sql .= " SIGNED";
        } elseif (isset($this->signedDataTypes[$realDataType['dataType']])) {
            $sql .= " UNSIGNED";
        }

        $sql .= $column->isNull() === true ? " NULL" : " NOT NULL";
        $sql .= $column->isAutoIncrement() === true ? " AUTO_INCREMENT": '';
        $defaultVal = $column->getDefault();
        if (isset($defaultVal)) {
            $sql .= $this->getDefaultSqlStr($column);
        }

        $sql .= $column->getUpdate() ? " ON UPDATE {$column->getUpdate()}" : "";

        return $sql;
    }

    /**
     * Returns SQL string containing the DEFAULT value
     *
     * @param Column $column
     * @return string
     */
    protected function getDefaultSqlStr(Column $column)
    {
        $default = $column->getDefault();
        if ($default !== "CURRENT_TIMESTAMP") {
            $default = "{$this->quote($default)}";
        } elseif (is_bool($default)) {
            $default = (int)$default;
        }

        return isset($default) ? " DEFAULT {$default}" : '';
    }

    /**
     * @param $dataType
     * @param null $size
     * @return array
     */
    protected function getRealDataType($dataType, $size = null)
    {

        switch($dataType) {
            case static::DATATYPE_STRING:
                return ['dataType' => 'varchar', 'size' => $size ? $size : static::DEFAULT_VARCHAR ];
                break;

            case static::DATATYPE_CHAR:
                return ['dataType' => $dataType, 'size' => $size ? $size : static::DEFAULT_CHAR ];
                break;

            case static::DATATYPE_TEXT:
            case static::DATATYPE_BLOB:
            case static::DATATYPE_MEDIUMTEXT:
            case static::DATATYPE_MEDIUMBLOB:
            case static::DATATYPE_LONGTEXT:
            case static::DATATYPE_LONGBLOB:
                return ['dataType' => $dataType];
                break;

            case static::DATATYPE_INT:
            case static::DATATYPE_INTEGER:
                return ['dataType' => 'int', 'size' => $size ? $size : static::DEFAULT_INT];
                break;

            case static::DATATYPE_SMALLINT:
            case static::DATATYPE_SMALLINTEGER:
                return ['dataType' => 'smallint', 'size' => $size ? $size : static::DEFAULT_SMALLINT ];
                break;

            case static::DATATYPE_MEDIUMINT:
            case static::DATATYPE_MEDIUMINTEGER:
                return ['dataType' => 'mediumint', 'size' => $size ? $size : static::DEFAULT_MEDIUMINT ];
                break;

            case static::DATATYPE_BIGINT:
            case static::DATATYPE_BIGINTEGER:
                return ['dataType' => 'bigint', 'size' => $size ? $size : static::DEFAULT_BIGINT ];
                break;

            case static::DATATYPE_FLOAT:
                return ['dataType' => 'float', 'size' => $size ? $size : array('precision' => static::DEFAULT_FLOAT_PRECISION, 'scale' => static::DEFAULT_FLOAT_SCALE) ];
                break;

            case static::DATATYPE_REAL:
            case static::DATATYPE_DOUBLE:
                return ['dataType' => 'double', 'size' => $size ? $size : array('precision' => static::DEFAULT_DOUBLE_PRECISION, 'scale' => static::DEFAULT_DOUBLE_SCALE) ];
                break;

            case static::DATATYPE_NUMERIC:
            case static::DATATYPE_DECIMAL:
                return ['dataType' => 'decimal', 'size' => $size ? $size : array('precision' => static::DEFAULT_DECIMAL_PRECISION, 'scale' => static::DEFAULT_DECIMAL_SCALE) ];
                break;

            case static::DATATYPE_DATETIME:
                return ['dataType' => 'datetime' ];
                break;

            case static::DATATYPE_TIMESTAMP:
                return ['dataType' => 'timestamp' ];
                break;

            case static::DATATYPE_TIME:
                return ['dataType' => 'time' ];
                break;

            case static::DATATYPE_DATE:
                return ['dataType' => 'date' ];
                break;

            case static::DATATYPE_BOOLEAN:
                return ['dataType' => 'tinyint', 'limit' => 1 ];
                break;

            case static::DATATYPE_UUID:
                return ['dataType' => 'char', 'limit' => 36 ];

            case static::DATATYPE_GEOMETRY:
            case static::DATATYPE_POINT:
            case static::DATATYPE_LINESTRING:
            case static::DATATYPE_POLYGON:
                return ['dataType' => $dataType ];
                break;

            case static::DATATYPE_ENUM:
                return ['dataType' => 'enum' ];
                break;

            case static::DATATYPE_SET:
                return ['dataType' => 'set' ];
                break;

            default:
                throw new \RuntimeException('The type: "' . $dataType . '" is not supported.');

        }
    }

    /**
     * {@inheritdoc}
     */
    public function dropTable($tableName)
    {
        $tableName = $this->quote($tableName);
        $this->execute("DROP TABLE IF EXISTS {$tableName}");
    }

    /**
     * @param $tableName
     * @param array|Where[] $conditions
     */
    public function dropRows($tableName, $conditions = [])
    {
        $tableName = $this->quote($tableName);
        $query = "DELETE FROM {$tableName}";
        $build = $this->buildWhere($conditions);
        $query .= $build['query'];
        $this->execute($query, $build['params']);
    }

    /**
     * {@inheritdoc}
     */
    public function getTablePrimaryKeys($tableName)
    {
        $result = false;
        if ($this->hasTable($tableName)) {
            $result = $this->query("SHOW KEYS FROM {$this->quote($tableName)} WHERE Key_name = 'PRIMARY'")->fetchAll();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableConstraints($tableName)
    {
        $result = false;
        if ($this->hasTable($tableName)) {
            $result = $this->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE where TABLE_NAME = {$this->getConnection()->quote($tableName)} AND REFERENCED_TABLE_NAME != 'NULL'")->fetchAll(\PDO::FETCH_COLUMN);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function tableHasForeignKeys($tableName)
    {
        return sizeof($this->getTableConstraints($tableName)) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function dropForeignKey($tableName, $constraints = [])
    {
        $return = false;
        $unique = [];

        if (!$this->hasTable($tableName)) {
            return $return;
        }

        if (empty($constraint)) {
            $tableConstraints = $this->getTableConstraints($tableName);
        } else {
            $tableConstraints = $constraints;
        }

        if (empty($tableConstraints)) {
            throw new \InvalidArgumentException("No Constraints found on table: {$tableName}.");
        }

        $this->getConnection()->beginTransaction();
        foreach ($tableConstraints as $constraint) {
            if (!in_array($constraint, $unique)) {
                $this->execute("ALTER TABLE {$this->quote($tableName)} DROP FOREIGN KEY {$this->quote($constraint)}");
                $unique[] = $constraint;
            }
        }

        try {
            $this->getConnection()->commit();
            $return = true;
        } catch(\PDOException $e) {
            $this->getConnection()->rollBack();
            throw new \PDOException($e->getMessage(),$e->getMessage(), $e->getPrevious());
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function dropTableWithForeignKeys($tableName, $constraints = [])
    {
        $this->dropForeignKey($tableName, $constraints);
        $this->dropTable($tableName);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update($tableName, array $data, $conditions = [])
    {
        $params = [];
        $query = "UPDATE {$this->quote($tableName)} SET ";

        foreach ($data as $column => $value) {
            $query .= $column . '=:' . $column . ',';
            $params[':' . $column] = $value;
        }

        $query = rtrim($query, ',');

        if (!empty($conditions)) {
            $build = $this->buildWhere($conditions);
            $query .= $build['query'];
            $params = array_merge($params, $build['params']);

        } else {
            throw new \LogicException('Update cannot be performed without a Where clause to determine the row(s) to be updated!');
        }

        return $this->execute($query, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyColumn($tableName, $columnName, Column $newColumn)
    {
        $sql = "ALTER TABLE {$this->quote($tableName)} CHANGE {$this->quote($columnName)} {$this->quote($newColumn->getName())} {$this->getColumnDefinition($newColumn)}";
        $after = $newColumn->getAfter();
        if (isset($after)) {
            $sql .= " AFTER {$after}";
        }
        $this->execute($sql);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn($tableName, Column $newColumn)
    {
        if ($tableName instanceof Table) {
            $tableName = $tableName->getName();
        }

        $sql = "ALTER TABLE {$this->quote($tableName)} ADD {$this->quote($newColumn->getName())} {$this->getColumnDefinition($newColumn)}";
        $after = $newColumn->getAfter();
        if (isset($after)) {
            $sql .= " AFTER {$after}";
        }
        $this->execute($sql);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTable($tableName)
    {
        return $this->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$tableName}'")->rowCount() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function dropColumn($tableName, $columnName)
    {
        $this->execute("ALTER TABLE {$this->quote($tableName)} DROP COLUMN {$this->quote($columnName)}");
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function createDatabase($dbName, array $options = [])
    {
        $charset = $options['charset'] ? $options['charset'] : 'utf8';
        $sql = "CREATE DATABASE {$this->quote($dbName)} DEFAULT CHARACTER SET {$charset}";
        if (isset($options['collate'])) {
            $sql .= " COLLATE {$options['collate']}";
        }

        $this->execute($sql);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function dropDatabase($dbName)
    {
        $this->execute("DROP DATABASE IF EXISTS {$dbName}");
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllRows($tableName, $columns = [], $conditions = [], $orderBy = [], $groupBy = [], $isCount = false, $limit = false)
    {
        $params = [];
        $columns = empty($columns) ? '*' : implode(',', $columns);
        $columns = $isCount === true ? "COUNT({$columns})" : $columns;
        $query = "SELECT {$columns} FROM {$tableName}";

        if (!empty($conditions)) {
            $build = $this->buildWhere($conditions);
            $query .= $build['query'];
            $params = $build['params'];
        }

        if (!empty($orderBy)) {
            $query .= ' ORDER BY ';
            foreach($orderBy as $column => $order) {
                $query .= "{$this->quote($column)} {$order},";
            }
            $query = rtrim($query, ',');
        }

        if (!empty($groupBy)) {
            $query .= ' GROUP BY '. implode(',', $groupBy);
        }

        if ($limit !== false) {
            $query .= ' LIMIT '. (int)$limit;
        }

        return $this->execute($query, $params);
    }

    /**`
     * {@inheritdoc}
     */
    public function getAll($tableName, $columns = [], $conditions = [], $orderBy = [], $groupBy = [], $isCount = false, $limit = false)
    {
        $params = [];
        $columns = empty($columns) ? '*' : implode(',', $columns);
        $columns = $isCount === true ? "COUNT({$columns})" : $columns;
        $query = "SELECT {$columns} FROM {$tableName}";

        if (!empty($conditions)) {
            $build = $this->buildWhere($conditions);
            $query .= $build['query'];
            $params = $build['params'];
        }

        if (!empty($orderBy)) {
            $query .= ' ORDER BY ';
            foreach($orderBy as $column => $order) {
                $query .= "{$this->quote($column)} {$order},";
            }
            $query = rtrim($query, ',');
        }

        if (!empty($groupBy)) {
            $query .= ' GROUP BY '. implode(',', $groupBy);
        }

        if ($limit !== false) {
            $query .= ' LIMIT '. (int)$limit;
        }

        if (!empty($params)) {
            $prepared = $this->getPrepared($query);
            $result = $prepared->execute($params);
            if ($result === false) {
                throw new \PDOException("SQL Error: {$this->getConnection()->errorCode()} : {$this->getConnection()->errorInfo()[2]}");
            }
            return $prepared;
        } else {
            $result = $this->query($query);
            if ($result === false) {
                throw new \PDOException("SQL Error: {$this->getConnection()->errorCode()} : {$this->getConnection()->errorInfo()[2]}");
            }
            return $result;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getSaveableColumns()
    {
        return $this->saveableColumns;
    }

    /**
     * {@inheritdoc}
     */
    public function setSaveableColumns(array $columns)
    {
        $this->saveableColumns = $columns;
        return $this;
    }

    /**
     * @param array|Where[] $conditions
     * @return string
     */
    protected function buildWhere(array $conditions)
    {
        $query = ' WHERE ';
        $params = [];
        $build = [];
        $prevVal = null;

        foreach ($conditions as $key => $val) {
            if (is_int($key) && $val instanceof Where) {
                if ($prevVal !== null) {
                    if ($val->getColumn() === $prevVal->getColumn()) {
                        $query .= ' OR ';
                    } else {
                        $query .= ' AND ';
                    }
                }
                $prevVal = $val;
                $query .= $val->getSql();
            } elseif (is_string($key)) {
                if ($prevVal !== null) {
                    if ($key === $prevVal) {
                        $query .= ' OR ';
                    } else {
                        $query .= ' AND ';
                    }
                }
                $prevVal = $key;
                $query .= $key . '=:' . $key;
                $params[':' . $key] = $val;
            }
        }

        $query = rtrim($query, ' AND ');
        $build['query'] = $query;
        $build['params'] = $params;

        return $build;
    }

    public function setParam(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }
}