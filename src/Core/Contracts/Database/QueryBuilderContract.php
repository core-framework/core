<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 13/02/16
 * Time: 1:53 PM
 */

namespace Core\Contracts\Database;


interface QueryBuilderContract
{
    /**
     * @param bool|false|int $index
     * @return array
     */
    public function get($index = false);

    /**
     * @param $column
     * @param $value
     * @param string $equator
     * @return $this
     */
    public function where($column, $value, $equator = '=');

    /**
     * @param $column
     * @return $this
     */
    public function groupBy($column);

    /**
     * @param $conditions
     * @param string $orderType
     * @return $this
     */
    public function orderBy($conditions, $orderType = 'DESC');

    /**
     * @param $column
     * @return $this
     */
    public function addColumn($column);
}