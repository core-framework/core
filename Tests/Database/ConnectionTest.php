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

namespace Core\Tests\Database;


use Core\Database\Connection;

class ConnectionTest extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var \PDO
     */
    private static $pdo = null;
    /**
     * @var Connection|null $conn
     */
    private $conn = null;
    /**
     * @var array
     */
    public static $conf;

    public static function getConfig()
    {
        if (!isset(self::$conf)) {
            self::$conf = $conf = require('config.php');
        }

        return self::$conf;
    }

    public function getConnection()
    {
        if ($this->conn === null) {

            $conf = $this->getConfig();

            if (self::$pdo == null) {
                self::$pdo = new Connection($conf);
            }

            $this->conn = $this->createDefaultDBConnection(self::$pdo, 'test');
        }

        return $this->conn;
    }

    public function getDataSet($file = 'testDBFixture')
    {
        return $this->createMySQLXMLDataSet(__DIR__ . "/Fixtures/{$file}.xml");
    }

    public function loadDataSet($dataSet)
    {
        // set the new dataset
        $this->getDatabaseTester()->setDataSet($dataSet);
        // call setUp which adds the rows
        $this->getDatabaseTester()->onSetUp();
    }

    public function getSetUpOperation()
    {
        return $this->getOperations()->CLEAN_INSERT();
    }

    /**
     * SetUp
     */
    public function setUp()
    {
        $conn = $this->getConnection();
        $pdo = $conn->getConnection();

        // set up tables
        $fixtureDataSet = $this->getDataSet();
        foreach ($fixtureDataSet->getTableNames() as $table) {
            // drop table
            $pdo->exec("DROP TABLE IF EXISTS {$this->quote($table)};");
            // recreate table
            $meta = $fixtureDataSet->getTableMetaData($table);
            $create = "CREATE TABLE IF NOT EXISTS {$this->quote($table)}";
            $cols = array();
            foreach ($meta->getColumns() as $col) {
                if ($col === 'imageDesc') {
                    $cols[] = "{$this->quote($col)} TEXT";
                } else {
                    $cols[] = "{$this->quote($col)} VARCHAR(255)";
                }
            }
            $create .= '('.implode(',', $cols).');';
            $pdo->exec($create);
        }

        parent::setUp();
    }

    /**
     * TearDown
     */
    public function tearDown()
    {
        $allTables = $this->getDataSet()->getTableNames();
        foreach ($allTables as $table) {
            // drop table
            $conn = $this->getConnection();
            $pdo = $conn->getConnection();
            $pdo->exec("DROP TABLE IF EXISTS {$this->quote($table)};");
        }

        parent::tearDown();
    }

    public function quote($string)
    {
        return "`{$string}`";
    }

    /**
     * @covers \Core\Database\Connection::__construct
     */
    public function testDatabaseHasUser()
    {
        $this->getConnection()->createDataSet(array('user'));
        $prod = $this->getDataSet();
        $resultingTable = $this->getConnection()->createQueryTable('user', 'SELECT * FROM user');
        $expectedTable = $this->getDataSet()->getTable('user');

        $this->assertTablesEqual($expectedTable, $resultingTable);
    }
}
