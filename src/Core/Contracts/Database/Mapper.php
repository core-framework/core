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

namespace Core\Contracts\Database;

use Core\Database\Column;
use Core\Database\Table;
use Core\Database\Where;

interface Mapper
{
    const DATATYPE_STRING         = 'string';
    const DATATYPE_CHAR           = 'char';
    const DATATYPE_VARCHAR        = 'varchar';
    const DATATYPE_TEXT           = 'text';
    const DATATYPE_BLOB           = 'blob';
    const DATATYPE_TINYTEXT       = 'tintytext';
    const DATATYPE_TINYBLOB       = 'tinyblob';
    const DATATYPE_MEDIUMTEXT     = 'mediumtext';
    const DATATYPE_MEDIUMBLOB     = 'mediumblob';
    const DATATYPE_LONGTEXT       = 'longtext';
    const DATATYPE_LONGBLOB       = 'longblob';
    const DATATYPE_INTEGER        = 'integer';
    const DATATYPE_INT            = 'int';
    const DATATYPE_TINYINT        = 'tinyint';
    const DATATYPE_TINYINTEGER    = 'tinyinteger';
    const DATATYPE_SMALLINT       = 'smallint';
    const DATATYPE_SMALLINTEGER   = 'smallinteger';
    const DATATYPE_MEDIUMINT      = 'mediumint';
    const DATATYPE_MEDIUMINTEGER  = 'mediuminteger';
    const DATATYPE_BIGINT         = 'bigint';
    const DATATYPE_BIGINTEGER     = 'biginteger';
    const DATATYPE_FLOAT          = 'float';
    const DATATYPE_DOUBLE         = 'double';
    const DATATYPE_REAL           = 'real';
    const DATATYPE_NUMERIC        = 'numeric';
    const DATATYPE_DECIMAL        = 'decimal';
    const DATATYPE_DATETIME       = 'datetime';
    const DATATYPE_TIMESTAMP      = 'timestamp';
    const DATATYPE_TIME           = 'time';
    const DATATYPE_DATE           = 'date';
    const DATATYPE_BINARY         = 'binary';
    const DATATYPE_BOOLEAN        = 'boolean';
    const DATATYPE_JSON           = 'json';
    const DATATYPE_JSONB          = 'jsonb';
    const DATATYPE_UUID           = 'uuid';
    const DATATYPE_FILESTREAM     = 'filestream';

    // Geospatial database types
    const DATATYPE_GEOMETRY       = 'geometry';
    const DATATYPE_POINT          = 'point';
    const DATATYPE_LINESTRING     = 'linestring';
    const DATATYPE_POLYGON        = 'polygon';

	// only for mysql so far
    const DATATYPE_ENUM           = 'enum';
    const DATATYPE_SET            = 'set';

    const CREATED_AT = 'created_at';
    const MODIFIED_AT = 'modified_at';
    const DELETED_AT = 'deleted_at';

    public function getConfig();

    /**
     * @return \Core\Database\Connection
     */
    public function getConnection();

    /**
     * SQL begin transaction
     */
    public function beginTransaction();

    /**
     * SQL commit Transaction
     */
    public function commit();

    /**
     * SQL rollback Transaction
     */
    public function rollback();

    /**
     * @param $sql
     * @param $params
     * @return int
     */
    public function execute($sql, $params = []);

    /**
     * @param $sql
     * @return mixed
     */
    public function getPrepared($sql);

    /**
     * @param $sql
     * @return mixed
     */
    public function query($sql);

    /**
     * Executes the MySQL truncate table command
     *
     * @param $tableName
     */
    public function truncate($tableName);

    /**
     * Executes a SELECT statement with given params/conditions
     *
     * @param $tableName
     * @param array $columns
     * @param array $conditions
     * @param array $orderBy
     * @param array $groupBy
     * @param bool $isCount
     * @param bool|int $limit
     * @return mixed
     */
    public function getAllRows($tableName, $columns = [], $conditions = [], $orderBy = [], $groupBy = [], $isCount = false, $limit = false);

    /**
     * @param $tableName
     * @param array $columns
     * @param array $conditions
     * @param array $orderBy
     * @param array $groupBy
     * @param bool|false $isCount
     * @param bool|false $limit
     * @return mixed|\PDOStatement
     */
    public function getAll($tableName, $columns = [], $conditions = [], $orderBy = [], $groupBy = [], $isCount = false, $limit = false);

    /**
     * Executes the MySQL Insert table command
     *
     * @param Table $table
     * @param array $data data must be an array of array(rows) like:
     * array(
     *      array('row1_column1Value', 'row1_column2value' ..... ),
     *      array('row2_column1Value', 'row2_column2value' ..... ),
     *      ......
     * )
     * @return mixed
     */
    public function insert(Table $table, $data);

    /**
     * Executes the MySQL create table command
     *
     * @param Table $table
     * @return mixed
     */
    public function create(Table $table);

    /**
     * Get table primary keys from Database
     *
     * @param $tableName
     * @return array|bool
     */
    public function getTablePrimaryKeys($tableName);

    /**
     * Get table foreign key constraint names from database
     *
     * @param $tableName
     * @return array|bool
     */
    public function getTableConstraints($tableName);

    /**
     * @param $tableName
     * @return bool
     */
    public function tableHasForeignKeys($tableName);

    /**
     * Drop table constraints
     *
     * @param $tableName
     * @param array $constraints
     * @return bool
     */
    public function dropForeignKey($tableName, $constraints = []);

    /**
     * Drop table containing foreign key constraints
     *
     * @param $tableName
     * @param array $constraints
     * @return bool
     */
    public function dropTableWithForeignKeys($tableName, $constraints = []);

    /**
     * @param string $tableName
     * @param array $data Data must be of the form:
     * array(
     *      'columnName' => 'value',
     *      .....
     * )
     * @param array|where[] $conditions
     *
     * @return bool
     */
    public function update($tableName, array $data, $conditions = []);

    /**
     * Modify column specification/definition
     *
     * @param $tableName
     * @param $columnName
     * @param Column $newColumn
     * @return bool
     */
    public function modifyColumn($tableName, $columnName, Column $newColumn);

    /**
     * Add new column to existing Table
     *
     * @param string|Table $tableName
     * @param Column $newColumn
     * @return bool
     */
    public function addColumn($tableName, Column $newColumn);

    /**
     * Checks if table exists in database
     *
     * @param $tableName
     * @return mixed
     */
    public function hasTable($tableName);

    /**
     * Deletes(drops) the given table from database
     *
     * @param $tableName
     * @return mixed
     */
    public function dropTable($tableName);

    /**
     * Deletes(drops) given table column in database
     *
     * @param $tableName
     * @param $columnName
     * @return bool
     */
    public function dropColumn($tableName, $columnName);

    /**
     * Creates a new Database
     *
     * @param $dbName
     * @param array $options Options are 'charset' && 'collate'
     * @return mixed
     */
    public function createDatabase($dbName, array $options = []);

    /**
     * Delete database
     *
     * @param $dbName
     * @return bool
     */
    public function dropDatabase($dbName);

    /**
     * @param $tableName
     * @param array|Where[] $conditions
     */
    public function dropRows($tableName, $conditions = []);

    /**
     * @return array
     */
    public function getSaveableColumns();

    /**
     * @param array $columns
     * @return $this
     */
    public function setSaveableColumns(array $columns);
}