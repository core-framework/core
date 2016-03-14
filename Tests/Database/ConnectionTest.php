<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 29/10/15
 * Time: 10:43 AM
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
     * @var Array
     */
    public static $conf;

    public static function getConfig()
    {
        if (!isset(self::$conf)) {
            $dbConfPath = _ROOT . '/config/db.conf.php';
            if (is_readable($dbConfPath)) {
                self::$conf = $conf = require($dbConfPath);
            } else {
                self::$conf = $conf = require('config.php');
            }
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
