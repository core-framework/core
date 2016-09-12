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


namespace Core\Database\Migration;

use Core\Contracts\Database\Mapper;
use Core\Contracts\Database\Migration\Migration as MigrationInterface;
use Core\Database\Table;

abstract class AbstractMigration implements MigrationInterface
{
    protected $mapper;

    /**
     * @inheritDoc
     */
    final function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @inheritdoc
     */
    abstract function up();

    /**
     * @inheritdoc
     */
    abstract function down();

    /**
     * @inheritdoc
     */
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function hasTable($tableName)
    {
        return $this->mapper->hasTable($tableName);
    }

    public function table($tableName)
    {
        return new Table($tableName, [], $this->getMapper());
    }

    public function dropTable($tableName)
    {
        return $this->mapper->dropTable($tableName);
    }

    public function execute($sql, $params = [])
    {
        return $this->mapper->execute($sql, $params);
    }

    public function query($sql)
    {
        return $this->mapper->query($sql);
    }

    public function fetchAll($tableName, $columns = [], $conditions = [], $orderBy = [], $groupBy = [], $isCount = false, $limit = false)
    {
        return $this->mapper->getAllRows($tableName, $columns, $conditions, $orderBy, $groupBy, $isCount, $limit);
    }

    public function createDatabase($name, $options = [])
    {
        return $this->mapper->createDatabase($name, $options);
    }

    public function dropDatabase($name)
    {
        return $this->mapper->dropDatabase($name);
    }
}