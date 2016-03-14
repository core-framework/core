<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 26/01/16
 * Time: 2:18 PM
 */

namespace Core\Contracts;

use Core\Database\Connection;
use Core\Contracts\Database\LanguageContract;

interface ModelContract
{
    /**
     * Save to database
     *
     * @return mixed
     */
    public function save();

    /**
     * Update model table/database
     *
     * @return mixed
     */
    public function update();

    /**
     * Drop row permanently
     *
     * @return mixed
     */
    public function delete();

    /**
     * Mark row as deleted using 'deleted_at' column
     *
     * @return mixed
     */
    public function softDelete();

    /**
     * Moves row from current table to designated table
     *
     * @param string $tableName
     * @param bool $delete
     * @return bool
     */
    public function move($tableName, $delete = true);

    /**
     * @return Connection
     */
    public function getConnection();

    /**
     * Configure object
     *
     * @param $object
     * @param $props
     * @return mixed
     */
    public static function configure($object, $props);

    /**
     * @return LanguageContract
     */
    public static function getLanguage();

    /**
     * @return string
     */
    public static function getTableName();

    /**
     * @return array
     */
    public static function getConfig();

    /**
     * Gets All rows from database table
     *
     * @param array $conditions
     * @param array $orderBy
     * @param array $groupBy
     * @return mixed
     */
    public static function find($conditions = [], $orderBy = [], $groupBy = []);

    /**
     * Finds One Object matching conditions given or Fails with false
     *
     * @param array $conditions
     * @param array $columns
     * @param array $orderBy
     * @param array $groupBy
     * @return object|ModelContract
     */
    public static function findOne($conditions = [], $columns = [], $orderBy = [], $groupBy = []);

    /**
     * Finds One Object matching conditions given or Fails with an Exception
     *
     * @param array $conditions
     * @param array $columns
     * @param array $orderBy
     * @param array $groupBy
     * @return mixed
     * @throws \Exception
     */
    public static function findOneOrFail($conditions = [], $columns = [], $orderBy = [], $groupBy = []);

    /**
     * @param array $conditions
     * @param array $columns
     * @param array $orderBy
     * @param array $groupBy
     * @return mixed
     */
    public static function getCount($conditions = [], $columns = [], $orderBy = [], $groupBy = []);

    /**
     * @param $column
     * @param $matchValue
     * @param $matchType
     * @return mixed
     */
    public static function deleteRows($column, $matchValue, $matchType = '=');

    /**
     * Returns a collection of rows for the given query
     *
     * @param $query
     * @param array $params
     * @param bool $resultAsArray
     * @return array
     */
    public static function get($query, $params = [], $resultAsArray = false);

    /**
     * Converts given array
     *
     * @param array $rows
     * @return mixed
     */
    public static function toObject($rows = []);

    /**
     * @return array
     */
    public static function getCollection();

    /**
     * @param $collection
     * @return mixed
     */
    public static function setCollection($collection);

    /**
     * @param array $data
     * @return mixed
     */
    public static function make(array $data);

    /**
     * @return string
     */
    public static function getPrimaryKey();

    /**
     * @return string
     */
    public static function getDbName();
}