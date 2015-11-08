<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 29/10/15
 * Time: 10:43 AM
 */

namespace Database;


use Core\Database\Connection;

class ConnectionTest extends \PHPUnit_Extensions_Database_TestCase
{
    private static $pdo = null;
    private $conn = null;
    public $conf;

    public function getConnection()
    {
        if ($this->conn === null) {
            $dbConfPath = _ROOT . '/config/db.conf.php';
            if (is_readable($dbConfPath)) {
                $this->conf = $conf = require($dbConfPath);
            } else {
                $this->conf = $conf = [
                    'type' => 'mysql',
                    'db' => 'test',
                    'tables' => [],
                    'host' => '127.0.0.1',
                    'user' => 'root',
                    'pass' => 'qwedsa'
                ];
            }

            if (self::$pdo == null) {
                self::$pdo = new Connection($conf);
            }

            $this->conn = $this->createDefaultDBConnection(self::$pdo, 'test');
        }

        return $this->conn;
    }

    public function getDataSet()
    {
        return $this->createMySQLXMLDataSet(__DIR__ . "/Fixtures/testDBFixture.xml");
    }

    public function getSetUpOperation()
    {
        return $this->getOperations()->CLEAN_INSERT();
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
