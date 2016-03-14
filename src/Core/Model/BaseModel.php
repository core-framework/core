<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 28/06/15
 * Time: 12:59 AM
 */

namespace Core\Model;

use Core\Config\Config;
use Core\Contracts\Database\LanguageContract;
use Core\Contracts\ModelContract;
use Core\Database\Connection;
use Core\Database\Table;
use Core\Database\Where;

abstract class BaseModel implements ModelContract
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
     * @var array
     */
    protected static $config = [];
    /**
     * @var array
     */
    protected static $fillable = [];
    /**
     * @var array
     */
    protected static $saveable = [];
    /**
     * @var array
     */
    protected static $collection = [];
    /**
     * @var bool
     */
    protected static $useSoftDelete = false;

    /**
     * @var bool
     */
    protected static $returnRelationsAsBuilder = true;

    /**
     * @var LanguageContract
     */
    public static $language;

    /**
     * Save to database
     *
     * @return mixed
     */
    abstract public function save();

    /**
     * Update model table/database
     *
     * @return mixed
     */
    abstract public function update();

    /**
     * Deletes entry (row)
     *
     * @return mixed
     */
    abstract public function delete();

    /**
     * Marks entry (row) as deleted using 'deleted_at' column
     *
     * @return mixed
     */
    abstract public function softDelete();

    /**
     * Move entry to another table with same schema
     *
     * @param string $tableName
     * @param bool|true $delete
     * @return bool
     */
    abstract public function move($tableName, $delete = true);

    /**
     * Method called before Save method execution
     *
     * @return mixed
     */
    abstract protected function beforeSave();

    /**
     * Method called before Delete method execution
     *
     * @return mixed
     */
    abstract protected function beforeDelete();

    /**
     * @return Table
     */
    abstract public function getTableSchema();

    /**
     * @param LanguageContract|null $language
     */
    public function __construct(LanguageContract $language = null)
    {
        if (is_null($language)) {
            $language = $this->getLanguage();
        }
        $language->setSaveableColumns(static::$saveable);
        $this->setConfig($language->getConfig());
        $this->setLanguage($language);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->getLanguage()->getConnection();
    }

    /**
     * Configure object
     *
     * @param $object ModelContract
     * @param $props Array must be of the format:
     * array(
     *      'columnName' => 'columnValue',
     *      .....
     * )
     * @param $forceFill Boolean
     *
     * @return mixed
     */
    public static function configure($object, $props, $forceFill = false)
    {
        if (!empty(static::$fillable) && $forceFill === false) {
            foreach ($props as $name => $value) {
                if (in_array($name, static::$fillable) || $name === static::getPrimaryKey()) {
                    $object->$name = $value;
                }
            }
        } else {
            foreach ($props as $name => $value) {
                $object->$name = $value;
            }
        }

        return $object;
    }

    /**
     * @return LanguageContract
     */
    public static function getLanguage()
    {
        if (!isset(static::$language)) {
            self::setLanguage(self::getLanguageFromConfig());
        }
        return static::$language;
    }

    /**
     * @param mixed $language
     */
    public static function setLanguage(LanguageContract $language)
    {
        self::$language = $language;
    }

    /**
     * @return LanguageContract
     */
    public static function getLanguageFromConfig()
    {
        $config = self::getConfig();
        $type = isset($config['type']) ? $config['type'] : 'mysql';
        $language = self::getLanguageClass($type);
        return new $language();
    }

    /**
     * @param $type
     * @return string
     */
    public static function getLanguageClass($type)
    {
        switch ($type) {
            case 'mysql':
                return '\Core\Database\Language\MySqlLanguage';
                break;

            default:
                return '\Core\Database\Language\\' . ucfirst($type) . 'Language';
                break;
        }
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        if (empty(self::$config) && !empty(Config::getDatabase())) {
            self::setConfig(Config::getDatabase());
        }

        return self::$config;
    }

    /**
     * @param array $config
     */
    public static function setConfig($config)
    {
        self::$config = $config;
    }

    /**
     * Gets All rows from database table
     *
     * @param array $conditions
     * @param array $columns
     * @param array $orderBy
     * @param array $groupBy
     * @return mixed
     */
    public static function find($conditions = [], $columns = [], $orderBy = [], $groupBy = [])
    {
        $result = static::query($conditions, $columns, $orderBy, $groupBy);
        if (empty($result)) {
            return false;
        }
        return static::toObject($result);
    }

    public static function findOrFail($conditions = [], $columns = [], $orderBy = [], $groupBy = [])
    {
        $result = static::query($conditions, $columns, $orderBy, $groupBy);
        if (empty($result)) {
            throw new \Exception('No results found.', 404);
        }
        return static::toObject($result);
    }

    /**
     * {@inheritdoc}
     */
    public static function findOne($conditions = [], $columns = [], $orderBy = [], $groupBy = [])
    {
        $result = static::query($conditions, $columns, $orderBy, $groupBy, false, true);
        if (empty($result)) {
            return false;
        }
        return static::toObject($result)[0];
    }

    /**
     * {@inheritdoc}
     */
    public static function findOneOrFail($conditions = [], $columns = [], $orderBy = [], $groupBy = [])
    {
        $result = static::query($conditions, $columns, $orderBy, $groupBy, false, true);
        if (empty($result)) {
            throw new \Exception('No results found.', 404);
        }
        return static::toObject($result)[0];
    }

    /**
     * {@inheritdoc}
     */
    public static function getCount($conditions = [], $columns = [], $orderBy = [], $groupBy = [])
    {
        $result = static::query($conditions, $columns, $orderBy, $groupBy, true, true);
        return array_values($result[0])[0];
    }

    /**
     * {@inheritdoc}
     */
    public static function get($query, $params = [], $resultAsArray = false)
    {
        $prep = static::getLanguage()->getPrepared($query);
        $prep->execute($params);
        $result = $prep->fetchAll(\PDO::FETCH_ASSOC);
        if ($resultAsArray === false) {
            $collection = self::toObject($result);
            return static::setCollection($collection);
        }
        return static::setCollection($result);
    }

    /**
     * @param array $conditions
     * @param array $columns
     * @param array $orderBy
     * @param array $groupBy
     * @param bool $isCount
     * @param bool $limit
     * @return mixed
     */
    public static function query(
        $conditions = [],
        $columns = [],
        $orderBy = [],
        $groupBy = [],
        $isCount = false,
        $limit = false
    ) {
        $result = static::getLanguage()->getAllRows(
            static::$tableName,
            $columns,
            $conditions,
            $orderBy,
            $groupBy,
            $isCount,
            $limit
        );

        return $result;
    }

    /**
     * @param $column
     * @param $matchValue
     * @param $matchType
     * @return mixed
     */
    public static function deleteRows($column, $matchValue, $matchType = '=')
    {
        $where = new Where($column, $matchValue, $matchType);
        $result = static::getLanguage()->dropRows(self::getTableName(), [$where]);
        return $result;
    }

    /**
     * @param $column
     * @param $matchValue
     * @param string $matchType
     * @return bool
     */
    protected static function softDeleteRows($column, $matchValue, $matchType = '=')
    {
        if (!static::$useSoftDelete) {
            throw new \LogicException('Soft Delete must be explicitly set to true before you can use it.');
        }
        $where = new Where($column, $matchValue, $matchType);
        return static::getLanguage()->update(static::getTableName(), ['deleted_at' => 'now()'], [$where]);
    }

    /**
     * {@inheritdoc}
     */
    public static function toObject($rows = [])
    {
        if (empty($rows) && empty(static::$collection)) {
            throw new \InvalidArgumentException(
                '`toObject` method argument cannot be empty if collection was not previously set.'
            );
        }

        if (empty($rows)) {
            $rows = static::getCollection();
        }

        $collection = [];
        $className = get_called_class();
        foreach ($rows as $row) {
            if (is_object($row)) {
                throw new \InvalidArgumentException(
                    '`toObject` method required array of rows(array), array of object given.'
                );
            }
            $item = new $className();
            $item = static::configure($item, $row);
            array_push($collection, $item);
        }

        return static::setCollection($collection);
    }

    /**
     * @param $columnName
     * @param $matchValue
     * @param string $equator
     * @return mixed
     */
    public static function where($columnName, $matchValue, $equator = '=')
    {
        $where = new Where($columnName, $matchValue, $equator);
        return static::find([$where]);
    }

    /**
     * Converts object to Array
     *
     * @return array
     */
    public function toArray()
    {
        return (array)$this;
    }

    /**
     * @return array
     */
    public static function getCollection()
    {
        return static::$collection;
    }

    /**
     * @param $collection
     * @return mixed
     */
    public static function setCollection($collection)
    {
        return static::$collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTableName()
    {
        $tableName = empty(static::$tableName) === false ? static::$tableName : __CLASS__;
        return $tableName;
    }

    /**
     * @param string $tableName
     */
    public static function setTableName($tableName)
    {
        static::$tableName = $tableName;
    }

    /**
     * @return string
     */
    public static function getPrimaryKey()
    {
        return static::$primaryKey;
    }

    /**
     * @param string $primaryKey
     */
    public static function setPrimaryKey($primaryKey)
    {
        static::$primaryKey = $primaryKey;
    }

    /**
     * @return string
     */
    public static function getDbName()
    {
        return static::$dbName;
    }

    /**
     * @param string $dbName
     */
    public function setDbName($dbName)
    {
        static::$dbName = $dbName;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public static function make(array $data)
    {
        $class = get_called_class();
        return $class($data);
    }

    /**
     * @param boolean $bool
     */
    public static function setSoftDelete($bool)
    {
        $bool = boolval($bool);
        static::$useSoftDelete = $bool;
    }

    /**
     * @return array
     */
    public static function getFillable()
    {
        return static::$fillable;
    }
}