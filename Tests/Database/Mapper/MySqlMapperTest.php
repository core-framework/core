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

namespace Core\Tests\Database\Language;


use Core\Database\Column;
use Core\Database\Mapper\MySqlMapper;
use Core\Database\Table;
use Core\Tests\Database\ConnectionTest;

class MySqlMapperTest extends ConnectionTest
{
    /**
     * @var Table
     */
    public $table;
    /**
     * @var MySqlMapper
     */
    public $language;


    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        $language = new MySqlMapper(self::getConfig());
        $language->dropTableWithForeignKeys('product_order');
        $language->dropTable('product');
        $language->dropTable('customer');
    }

    public function createInit()
    {
        $this->language = new MySqlMapper($this->getConfig());
        $this->table = new Table('user', [], $this->language);
        $this->table->addColumn('id', 'integer', ['size' => 11])
            ->addColumn('fname')
            ->addColumn('lname')
            ->addColumn('name')
            ->addColumn('fbLogin')
            ->addColumn('userId')
            ->addColumn('fb_likes')
            ->addColumn('twttr_share')
            ->addColumn('email')
            ->addColumn('email_hash')
            ->addColumn('pass_hash')
            ->addColumn('salt')
            ->addColumn('dashboardUrl')
            ->addColumn('uploadUrl')
            ->addColumn('imageUrl')
            ->addColumn('imageThumbUrl')
            ->addColumn('imageDesc')
            ->addColumn('imageSubmitted')
            ->addColumn('register_date')
            ->addColumn('submitted_date')
            ->addColumn('modified_date');
    }

    /**
     * @covers \Core\Database\Mapper\MySqlMapperMapper::truncate
     * @covers \Core\Database\Mapper\MySqlMapperMapper::insert
     */
    public function testInsert()
    {
        $this->getConnection()->createDataSet(array('user'));

        $data = [
            [1, "Shalom", "Sam", "Shalom Sam", null, "shalom5634d10f9a663",0,0, "test@test.com", null, "$2y$13$6YY1DczQim4JsRDTN3Cb0Oa.TtCUBDfoTZW.Cwj4MGQEV1rQkRV6y", "$2y$13$6YY1DczQim4JsRDTN3Cb0c",'/dashboard/test10_55', '/upload/test10_552bb', '/images/users/test10', '/images/users/test10', null, null, "2015-10-31 20:02:48", "2015-10-31 20:02:48", "2015-10-31 20:02:48"]
        ];
        $this->createInit();
        $this->language->truncate('user');
        $this->language->insert($this->table, $data);

        $resultingTable = $this->getConnection()->createQueryTable('user', "SELECT * FROM user WHERE {$this->quote('fname')}='Shalom'");

        $expectedTable = $this->getDataSet('testInsertUserFixture')->getTable('user');
        $this->assertTablesEqual($expectedTable, $resultingTable);
    }

    /**
     * @covers \Core\Database\Mapper\MySqlMapper::__construct
     * @covers \Core\Database\Mapper\MySqlMapper::dropTable
     * @covers \Core\Database\Mapper\MySqlMapper::dropTableWithForeignKeys
     * @covers \Core\Database\Table::__construct
     * @covers \Core\Database\Table::addForeignKey
     * @covers \Core\Database\Table::addColumn
     */
    public function testCreate()
    {
        $language = new MySqlMapper($this->getConfig());
        $language->dropTableWithForeignKeys('product_order');
        $language->dropTable('product');
        $language->dropTable('customer');


        $product = new Table('product', ['engine' => 'INNODB'], $this->language);
        $product->addColumn('id', 'integer', ['null' => false, 'primaryKey' => true, 'autoIncrement' => true])
            ->addColumn('category', 'integer', ['null' => false, 'primaryKey' => true])
            ->addColumn('price', 'decimal');
        $result1 =  $language->create($product);

        $customer = new Table('customer', ['engine' => 'INNODB'], $this->language);
        $customer->addColumn('id', 'integer', ['primaryKey' => true]);
        $result2 = $language->create($customer);

        $productOrder = new Table('product_order', ['engine' => 'INNODB'], $this->language);
        $productOrder->addColumn('no', 'integer', ['null' => false, 'primaryKey' => true, 'autoIncrement' => true])
            ->addColumn('product_category', 'integer', ['null' => false])
            ->addColumn('product_id', 'integer', ['null' => false])
            ->addColumn('customer_id', 'integer', ['null' => false])
            ->addForeignKey(['product_category', 'product_id'], 'product', ['id', 'category'], ['onUpdate' => 'cascade', 'onDelete' => 'restrict'])
            ->addForeignKey('customer_id', 'customer', 'id');
        $result3 = $language->create($productOrder);

        $this->assertTrue($result1);
        $this->assertTrue($result2);
        $this->assertTrue($result3);
    }

    /**
     * @covers \Core\Database\Mapper\MySqlMapper::__construct
     * @covers \Core\Database\Mapper\MySqlMapper::modifyColumn
     * @covers \Core\Database\Column::__construct
     * @covers \Core\Database\Column::setName
     * @covers \Core\Database\Column::setDataType
     * @covers \Core\Database\Column::setPrecision
     * @covers \Core\Database\Column::setScale
     */
    public function testModify()
    {
        $language = new MySqlMapper($this->getConfig());
        $newColumn = new Column();
        $newColumn->setName('price')->setDataType('float')->setPrecision(20)->setScale(5);
        $result = $language->modifyColumn('product', 'price', $newColumn);

        $this->assertTrue($result);
    }

    /**
     * @covers \Core\Database\Mapper\MySqlMapper::__construct
     * @covers \Core\Database\Mapper\MySqlMapper::addColumn
     * @covers \Core\Database\Column::__construct
     * @covers \Core\Database\Column::setName
     * @covers \Core\Database\Column::setDataType
     * @covers \Core\Database\Column::setDefault
     * @covers \Core\Database\Column::setAfter
     */
    public function testAddColumn()
    {
        $this->language = new MySqlMapper($this->getConfig());
        $newColumn = new Column();
        $newColumn->setName('created_on')->setDataType('timestamp')->setDefault('CURRENT_TIMESTAMP')->setAfter('category');
        $result = $this->language->addColumn('product', $newColumn);
        $this->assertTrue($result);
    }

    /**
     * @covers \Core\Database\Mapper\MySqlMapper::dropColumn
     */
    public function testDropColumn()
    {
        $language = new MySqlMapper($this->getConfig());
        $result = $language->dropColumn('product', 'created_on');
        $this->assertTrue($result);
    }

    /**
     * @covers \Core\Database\Mapper\MySqlMapper::getAllRows
     */
    public function testGetAllRows()
    {
        $language = new MySqlMapper($this->getConfig());
        $result = $language->getAllRows('user');
        $expected = $this->getDataSet()->getTable('user')->getRow(0);
        $this->assertEquals($expected, $result[0]);

        $result2 = $language->getAllRows('user', ['name','email']);
        $this->assertArrayHasKey('name', $result2[0]);
        $this->assertArrayHasKey('email', $result2[0]);
        $this->assertArrayNotHasKey('fname', $result2[0]);
        $this->assertArrayNotHasKey('lname', $result2[0]);
        $this->assertArrayNotHasKey('salt', $result2[0]);
    }

}
