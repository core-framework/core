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


namespace Core\Contracts\Database\Migration;


use Core\Contracts\Database\Mapper;

interface Migration
{

    /**
     * Migrate Up
     *
     * @return void
     */
    public function up();

    /**
     * Migrate Down
     *
     * @return void
     */
    public function down();

    /**
     * Set Database Mapper
     *
     * @param Mapper $mapper
     * @return $this
     */
    public function setMapper(Mapper $mapper);

    /**
     * Gets database mapper
     *
     * @return Mapper
     */
    public function getMapper();

    /**
     * Checks if table exists
     *
     * @param $tableName
     * @return bool
     */
    public function hasTable($tableName);

    /**
     * Returns an instance of <code>Core\Database\Table</code> Class
     *
     * @param $tableName
     * @return mixed
     */
    public function table($tableName);

    /**
     * Drop(delete) given table from database
     *
     * @param $tableName
     * @return mixed
     */
    public function dropTable($tableName);

    /**
     * Executes given SQL query
     *
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function execute($sql, $params = []);

    /**
     * Queries the database with given SQL query
     *
     * @param $sql
     * @return mixed
     */
    public function query($sql);

    /**
     * Fetches all rows from table based on given conditions and filters
     *
     * @param $tableName
     * @param array $columns
     * @param array $conditions
     * @param array $orderBy
     * @param array $groupBy
     * @param bool $isCount
     * @param bool $limit
     * @return mixed
     */
    public function fetchAll($tableName, $columns = [], $conditions = [], $orderBy = [], $groupBy = [], $isCount = false, $limit = false);

    /**
     * Creates a new database
     *
     * @param $name
     * @param array $options
     * @return mixed
     */
    public function createDatabase($name, $options = []);

    /**
     * Drops(deletes) a given database
     *
     * @param $name
     * @return mixed
     */
    public function dropDatabase($name);
}