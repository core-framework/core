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

namespace Core\Contracts;

use Core\Database\Connection;
use Core\Contracts\Database\Mapper;

interface ModelContract
{
    /**
     * Save to database
     *
     * @return bool
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
     * @return Mapper
     */
    public static function getMapper();

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
     * @return mixed
     */
    public static function getCount($conditions = [], $columns = []);

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