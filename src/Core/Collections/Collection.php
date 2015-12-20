<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 02/05/15
 * Time: 4:56 PM
 */

namespace Core\Collections;


use Core\Contracts\CollectionContract;

class Collection implements CollectionContract
{
    protected static $collectionName = '';
    protected static $dbName = '';

    /**
     * List of fields not to be saved or assigned to Collection Objects
     *
     * @var array
     */
    public static $fieldsBlackList = [];

    /**
     * @var \MongoClient $client
     */
    protected static $client;
    /**
     * @var string $primaryKey
     */
    protected static $primaryKey;
    /**
     * @var \MongoDB $db
     */
    protected static $db;

    /**
     * Collection Object Constructor
     *
     * @param array $fields
     * @param \MongoDB|null $db
     * @throws \ErrorException
     */
    public function __construct(array $fields = [], \MongoDB $db = null)
    {
        if (!is_null($db)) {
            self::$db = $db;
        } else {
            self::$db = $this->getConnection();
        }

        $this->configure($this, $fields);
    }

    /**
     * Attempts to assign given data to object properties
     *
     * @param $object
     * @param $data
     * @return mixed
     * @throws \ErrorException
     */
    public static function configure($object, $data)
    {
        if (!is_object($object)) {
            throw new \ErrorException('Invalid parameter given. Object expected.');
        }

        $props = get_object_vars($object);

        if (!empty($data)) {

            foreach ($props as $prop) {
                if (isset($data[$prop]) && !in_array($prop, self::$fieldsBlackList)) {
                    $object->$prop = $data[$prop];
                }
            }

        }

        return $object;
    }

    /**
     * Returns all collection object that match the given query(condition, etc.)
     *
     * @param array $condition
     * @param null $orderBy
     * @param null $limit
     * @return array
     * @throws \ErrorException
     */
    public static function findAll(array $condition = [], $orderBy = null, $limit = null)
    {
        $parameters = [];
        if (isset($condition) === true) {
            $parameters['condition'] = $condition;
        }
        if (isset($orderBy) === true) {
            $parameters['orderBy'] = $orderBy;
        }
        if (isset($limit) === true) {
            $parameters['limit'] = $limit;
        }

        return self::find($parameters);
    }

    /**
     * Returns an object matching given parameters(/condition)
     *
     * @param array|null $parameters
     * @return array
     * @throws \ErrorException
     */
    public static function find(array $parameters = null)
    {
        if (is_null($parameters) === false && is_array($parameters) === false) {
            throw new \ErrorException('Invalid parameters for find');
        }

        $className = get_called_class();
        $collection = new $className();

        return self::getResultSet($parameters, $collection);
    }

    /**
     * Gets a count of Collection result of a query
     *
     * @param array|null $condition
     * @return int
     * @throws \ErrorException
     */
    public static function getCount(array $condition = null)
    {
        if (is_null($condition) === false && is_array($condition) === false) {
            throw new \ErrorException('Invalid parameters for find');
        }

        $className = get_called_class();
        /** @var Collection $collection */
        $collection = new $className();
        $collectionName = $collection->getCollectionName();
        $db = $collection->getConnection();

        $mCollection = $db->$collectionName;

        if (isset($condition) === true) {
            return $mCollection->find($condition)->count();
        } else {
            return $mCollection->count();
        }
    }

    /**
     * Used to perform Mongo aggregate actions on database
     *
     * @param array $pipeline
     * @return array
     * @throws \ErrorException
     */
    public static function aggregate(array $pipeline)
    {
        if (is_null($pipeline) === false && is_array($pipeline) === false) {
            throw new \ErrorException('Invalid parameters for find');
        }


        $className = get_called_class();
        /** @var Collection $collection */
        $collection = new $className();
        $collectionName = $collection->getCollectionName();
        $db = $collection->getConnection();

        $mCollection = $db->$collectionName;

        return $mCollection->aggregate($pipeline);

    }

    /**
     * Gets a distinct result matching the given condition
     *
     * @param array $condition
     * @return array|bool
     * @throws \ErrorException
     */
    public static function distinct(array $condition)
    {
        if (is_string($condition) === false) {
            throw new \ErrorException('Invalid parameters for distinct');
        }

        /** @var Collection $collection */
        $mCollection = self::getCollection($condition);

        if (!isset($condition)) {
            throw new \ErrorException('Condition must be provided for a distinct call!');
        }

        return $mCollection->distinct($condition);
    }

    /**
     * Returns the Mongo Collection Object
     *
     * @param array|null $condition
     * @return \MongoCollection
     */
    protected static function getCollection(array $condition = null)
    {
        $className = get_called_class();
        /** @var Collection $collection */
        $collection = new $className($condition);
        $collectionName = $collection->getCollectionName();
        $db = $collection->getConnection();

        $mCollection = $db->$collectionName;

        return $mCollection;
    }

    /**
     * @param null $parameters
     * @param Collection $collection
     * @return array
     * @throws \ErrorException
     */
    public static function getResultSet($parameters = null, Collection $collection)
    {
        $collectionName = $collection->getCollectionName();
        $db = $collection->getConnection();
        /** @var \MongoCollection $mCollection */
        $mCollection = $db->$collectionName;

        if (isset($parameters['condition']) === true) {
            $condition = $parameters['condition'];
        } elseif (isset($parameters['query']) === true) {
            $condition = $parameters['query'];
        } else {
            $condition = [];
        }

        if (isset($parameters['fields'])) {
            $docCursor = $mCollection->find($condition, $parameters['fields']);
        } else {
            $docCursor = $mCollection->find($condition);
        }

        if (isset ($parameters['orderBy']) === true) {
            $docCursor->sort($parameters['$orderBy']);
        }

        if (isset($parameters['limit']) === true) {
            $docCursor->limit($parameters['limit']);
        }

        $docArr = iterator_to_array($docCursor);

        if (empty($docArr)) {
            return [];
        }

        return static::createObjectFromArr($docArr);

    }

    /**
     * @return string
     */
    protected function getCollectionName()
    {
        if (isset($this->collectionName) === false) {
            self::$collectionName = strtolower(get_class($this));
        }

        return self::$collectionName;
    }

    /**
     * Returns the MongoDB object
     *
     * @return \MongoDB
     */
    public function getConnection()
    {
        self::$client = $client = new \MongoClient();
        $dbName = self::getDbName();

        self::$db = $db = $client->$dbName;

        return $db;
    }

    /**
     * Returns the Database name
     *
     * @return string
     */
    public static function getDbName()
    {
        return static::$dbName;
    }

    /**
     * Converts each row (array) into object of the called Collection instance
     *
     * @param array $arr
     * @return array
     * @throws \ErrorException
     */
    protected static function createObjectFromArr(array $arr)
    {
        if (empty($arr)) {
            throw new \ErrorException('Array cannot be empty in createObjectFromArr');
        }

        $collectionArr = [];
        $class = get_called_class();
        foreach ($arr as $doc) {
            $collectionArr[] = new $class($doc);
        }

        return $collectionArr;
    }

    /**
     * Finds only one collection item
     *
     * @param array $condition
     * @return array
     * @throws \ErrorException
     */
    public static function findOne(array $condition = [])
    {
        if (empty($condition) && is_array($condition) === false) {
            throw new \ErrorException('Invalid parameters for find');
        }
        $condition['limit'] = 1;

        return static::find($condition);
    }

    /**
     * Save (write) to mongo database
     *
     * @throws \ErrorException
     */
    public function save()
    {
        $db = $this->getConnection();
        $collectionName = $this->getCollectionName();
        $this->beforeSave();

        if (empty($collectionName)) {
            throw new \ErrorException('Collection Name not specified.');
        }

        if (!$db instanceof \MongoDB) {
            throw new \ErrorException('Database not specified.');
        }

        /** @var \MongoCollection $collection */
        $collection = $db->$collectionName;
        if ($collection->insert($this) === false) {
            throw new \ErrorException('Unable to save to database.');
        }
    }

    /**
     * Actions to be performed before writing to database
     *
     * @param bool|true $unsetDates
     */
    public function beforeSave($unsetDates = true)
    {
        foreach (static::$fieldsBlackList as $column) {
            unset($this->$column);
        }

        if ($unsetDates === true) {
            unset($this->created_at);
            unset($this->modified_at);
        }
    }

    /**
     * Update (modify) to mongo database
     *
     * @param array $conditions
     * @param array $fields
     * @throws \ErrorException
     */
    public function update($conditions = [], $fields = [])
    {
        $db = $this->getConnection();
        $collectionName = $this->getCollectionName();
        $this->beforeSave();

        if (empty($collectionName)) {
            throw new \ErrorException('Collection Name not specified.');
        }

        if (!$db instanceof \MongoDB) {
            throw new \ErrorException('Database not specified.');
        }

        if (empty($conditions) === true) {
            $primaryKey = static::$primaryKey;
            $conditions = [$primaryKey => $this->$primaryKey];
        }

        if (empty($fields) === true) {
            $fields = (array)$this;
        }

        /** @var \MongoCollection $collection */
        $collection = $db->$collectionName;
        if ($collection->update($conditions, ['$set' => $fields])) {
            throw new \ErrorException('Unable to update collection');
        }
    }

    /**
     * @param $name
     * @throws \ErrorException
     */
    public static function setCollectionName($name)
    {
        if (is_string($name) === false) {
            throw new \ErrorException('Collection name must be of type string.');
        }
        self::$collectionName = $name;
    }
}