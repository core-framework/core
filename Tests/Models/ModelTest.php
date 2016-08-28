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

namespace Core\Tests\Models;

use Core\Database\Mapper\MySqlMapper;
use Core\Database\Table;
use Core\Database\Where;
use Core\Tests\Database\ConnectionTest;
use Core\Database\QueryBuilder;
use Core\Contracts\Database\Mapper;
use Core\Tests\Models\testModels\Comment;
use Core\Tests\Models\testModels\Employee;
use Core\Tests\Models\testModels\Phone;
use Core\Tests\Models\testModels\Post;
use Core\Tests\Models\testModels\User;

class ModelTest extends ConnectionTest
{
    /**
     * @var Mapper
     */
    public $mapping;

    public $data = [
        [1, 'Shalom', 'Sam', 'Shalom Sam', 29],
        [2, 'Shelley', 'Sam', 'Shelley Sam', 24],
        [3, 'John', 'Doe', 'John Doe', 30],
        [4, 'Jane', 'Doe', 'Jane Doe', 28],
        [5, 'Keith', 'Pinto', 'Keith Pinto', 27]
    ];

    public $phoneData = [
        [1, 1, 'iPhone'],
        [2, 2, 'Moto x'],
        [3, 3, 'Galaxy S3'],
        [4, 4, 'Galaxy supreme'],
        [5, 5, 'No phone']
    ];

    public $postData = [
        [1, 'Scientific Discoveries', 'Experiment without procedure, and we won’t accelerate a phenomenon.'],
        [2, 'Piracy at its best', 'Gutless, big yardarms fiery taste a salty, misty lass.']
    ];

    public $commentsData = [
        [1, 1, 'This some awesome post'],
        [2, 1, 'Waaatt!! wow this is great'],
        [3, 1, 'I LIKE!!!'],
        [4, 2, 'WoaaaH!!! I cant believe this is happening'],
        [5, 2, 'Noway, this is badd']
    ];

    public $employeeData = [
        [1, 'Robin', 'Jackman', 'Software Engineer', 5500],
        [2, 'Taylor', 'Edward', 'Software Architect', 7200],
        [3, 'Vivian', 'Dickens', 'Database Administrator', 6000],
        [4, 'Harry', 'Clifford', 'Database Administrator', 6800],
        [5, 'Eliza', 'Clifford', 'Software Engineer', 4780]
    ];

    public $educationData = [
        [1, 'BSc'],
        [2, 'MSc'],
        [3, 'PhD']
    ];

    public $employee_education_data = [
        [1,1],
        [2,1],
        [3,2],
        [3,3]
    ];

    public function getMapping()
    {
        if (!isset($this->mapping)) {
            $this->mapping = new MySqlMapper($this->getConfig());
        }
        return $this->mapping;
    }

    public function getPhoneTableSchema()
    {
        $table = new Table('phone', ['primaryKey' => 'id', 'foreignKey' => 'user_id']);
        $table->addColumn('id', 'integer', ['size' => 10, 'autoIncrement' => true])
            ->addColumn('user_id', 'integer', ['size' => 10])
            ->addColumn('phone', 'string', ['size' => 50])
            ->addTimestamps();

        return $table;
    }

    public function getPostsTableSchema()
    {
        $table = new Table('post', ['primaryKey' => 'id']);
        $table->addColumn('id', 'integer', ['size' => 10, 'autoIncrement' => true])
            ->addColumn('title', 'text')
            ->addColumn('body', 'text')
            ->addTimestamps();

        return $table;
    }

    public function getCommentsTableSchema()
    {
        $table = new Table('comment', ['primaryKey' => 'id', 'foreignKey' => 'post_id']);
        $table->addColumn('id', 'integer', ['size' => 10, 'autoIncrement' => true])
            ->addColumn('post_id', 'integer', ['size' => 10])
            ->addColumn('text', 'text')
            ->addTimestamps();

        return $table;
    }

    public function getEmployeeSchema()
    {
        $table = new Table('employee', ['primaryKey' => 'id']);
        $table->addColumn('id', 'integer', ['size' => 10, 'autoIncrement' => true])
            ->addColumn('first_name', 'string', ['size' => 200])
            ->addColumn('last_name', 'string', ['size' => 200])
            ->addColumn('job_title', 'string', ['size' => 200])
            ->addColumn('salary', 'integer', ['size' => 10])
            ->addTimestamps();

        return $table;
    }

    public function getEducationSchema()
    {
        $table = new Table('qualification', ['primaryKey' => 'id']);
        $table->addColumn('id', 'integer', ['size' => 10, 'autoIncrement' => true])
            ->addColumn('qualification', 'string', ['size' => 200])
            ->addTimestamps();

        return $table;
    }

    public function getEmployee_Education_Schema()
    {
        $table = new Table('employee_qualification');
        $table->addColumn('qualification_id', 'integer', ['size' => 10])
            ->addColumn('employee_id', 'integer', ['size' => 10]);

        return $table;
    }

    /**
     * @covers \Core\Database\Table::addColumn
     * @covers \Core\Database\Table::addTimestamps
     *
     * @return Table
     */
    public function getTableSchema()
    {
        $table = new Table('model_test_user', ['primaryKey' => 'id']);
        $table->addColumn('id', 'integer', ['size' => 10, 'autoIncrement' => true])
            ->addColumn('fname', 'string', ['size' => 200])
            ->addColumn('lname', 'string', ['size' => 200])
            ->addColumn('name', 'string', ['size' => 255])
            ->addColumn('age', 'integer', ['size' => 10])
            ->addTimestamps();

        return $table;
    }

    /**
     * @covers \Core\Database\Table::addColumn
     * @covers \Core\Database\Table::addTimestamps
     * @covers \Core\Database\Table::addDelete
     *
     * @return Table
     */
    public function getTable2Schema()
    {
        $table = new Table('soft_delete_test_user', ['primaryKey' => 'id']);
        $table->addColumn('id', 'integer', ['size' => 10, 'autoIncrement' => true])
            ->addColumn('fname', 'string', ['size' => 200])
            ->addColumn('lname', 'string', ['size' => 200])
            ->addColumn('name', 'string', ['size' => 255])
            ->addColumn('age', 'integer', ['size' => 10])
            ->addTimestamps()
            ->addDelete();

        return $table;
    }

    public static function setUpBeforeClass()
    {

    }

    /**
     * @covers \Core\Contracts\Database\LanguageContract::create
     * @covers \Core\Database\Language\MySqlLanguage::create
     */
    public function setUp()
    {
        $language = $this->getMapping();
        $table = $this->getTableSchema();
        $table2 = $this->getTable2Schema();
        $phoneTable = $this->getPhoneTableSchema();
        $postsTable = $this->getPostsTableSchema();
        $commentsTable = $this->getCommentsTableSchema();
        $employeeTable = $this->getEmployeeSchema();
        $educationTable = $this->getEducationSchema();
        $education_employeeTable = $this->getEmployee_Education_Schema();
        $language->create($table);
        $language->create($table2);
        $language->create($phoneTable);
        $language->create($postsTable);
        $language->create($commentsTable);
        $language->create($employeeTable);
        $language->create($educationTable);
        $language->create($education_employeeTable);
        $language->insert($table, $this->data);
        $language->insert($table2, $this->data);
        $language->insert($phoneTable, $this->phoneData);
        $language->insert($postsTable, $this->postData);
        $language->insert($commentsTable, $this->commentsData);
        $language->insert($employeeTable, $this->employeeData);
        $language->insert($educationTable, $this->educationData);
        $language->insert($education_employeeTable, $this->employee_education_data);
        parent::setUp();
    }

    /**
     * @covers \Core\Contracts\Database\LanguageContract::dropTable
     * @covers \Core\Database\Language\MySqlLanguage::dropTable
     */
    public function tearDown()
    {
        $language = $this->getMapping();
        $language->dropTable('model_test_user');
        $language->dropTable('soft_delete_test_user');
        $language->dropTable('comment');
        $language->dropTable('education');
        $language->dropTable('employee_qualification');
        $language->dropTable('employee');
        $language->dropTable('post');
        $language->dropTable('posts');
        $language->dropTable('comments');
        parent::tearDown();
    }

    /**
     * @covers \Core\Contracts\ModelContract::__construct
     * @covers \Core\Contracts\ModelContract::where
     * @covers \Core\Tests\Models\testModels\User::__construct
     * @covers \Core\Tests\Models\testModels\User::where
     */
    public function testModelFetch()
    {
        $user = new User();
        $this->assertInstanceOf('Core\Tests\Models\testModels\User', $user);

        $users = User::where('age', 29, '>');
        $this->assertInternalType('array', $users);
        foreach($users as $user) {
            $this->assertInstanceOf('Core\Contracts\ModelContract', $user);
            $this->assertObjectHasAttribute('age', $user);
            $this->assertAttributeGreaterThan(29, 'age', $user);
        }

    }

    /**
     * @covers \Core\Contracts\ModelContract::__construct
     * @covers \Core\Contracts\ModelContract::save
     *
     *
     * @throws \ErrorException
     */
    public function testModelSave()
    {
        $row = ['fname' => 'Money', 'lname' => 'Penny', 'name' => 'Money Penny', 'age' => 40];
        $user = new User($row);
        $this->assertInstanceOf('Core\Contracts\ModelContract', $user);
        $user->save();

        $testTable = $this->getConnection()->getConnection()->query("SELECT `fname`, `lname`, `name`, `age` FROM {$this->quote('model_test_user')}")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals($row, end($testTable));
    }

    /**
     * @covers \Core\Contracts\ModelContract::__construct
     * @covers \Core\Contracts\ModelContract::findOne
     * @covers \Core\Contracts\ModelContract::update
     *
     */
    public function testModelUpdate()
    {
        $user = User::findOne(['age' => 28]);
        $user->age = 31;
        $user->update();

        $result = $this->getConnection()->getConnection()->query("SELECT `age` FROM {$this->quote('model_test_user')} WHERE `fname` = '{$user->fname}'")->fetchAll(\PDO::FETCH_ASSOC);

        $user2 = User::findOne(['age' => 30]);
        $user2->age = 40;
        $user2->update();

        $result2 = $this->getConnection()->getConnection()->query("SELECT `age` FROM {$this->quote('model_test_user')} WHERE `fname` = '{$user2->fname}'")->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertEquals(31, $result[0]['age']);
        $this->assertEquals(40, $result2[0]['age']);
    }

    /**
     * @covers \Core\Contracts\ModelContract::findOne
     */
    public function testFindOneReturnsFalse()
    {
        $user = User::findOne(['age' => 100]);
        $this->assertFalse($user);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No results found.
     * @expectedExceptionCode 404
     */
    public function testFindOneOrFail()
    {
        $user = User::findOneOrFail(['age' => 100]);
    }

    /**
     * @covers \Core\Contracts\ModelContract::find
     */
    public function testFindReturnsFalse()
    {
        $user = User::find(['age' => 100]);
        $this->assertFalse($user);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No results found.
     * @expectedExceptionCode 404
     */
    public function testFindFail()
    {
        $user = User::findOrFail(['age' => 100]);
    }

    public function testCountWithCondition()
    {
        $count = User::getCount(['fname' => 'Shalom']);
        $this->assertEquals($count, 1);
    }

    public function testCountWithFalseCondition()
    {
        $count = User::getCount(['fname' => 'gib']);
        $this->assertEquals($count, 0);
    }

    /**
     * @covers \Core\Contracts\ModelContract::getCount
     * @covers \Core\Contracts\ModelContract::findOne
     * @covers \Core\Contracts\ModelContract::delete
     *
     */
    public function testDelete()
    {
        $countBefore = User::getCount();
        $user = User::findOne(['id' => 1]);
        $this->assertInstanceOf('Core\Contracts\ModelContract', $user);
        $user->delete();

        $countAfter = User::getCount();
        $result = $this->getConnection()->getConnection()->query("SELECT * FROM {$this->quote('model_test_user')} WHERE `id` = '{$user->id}'")->fetchAll();

        $this->assertEmpty($result);
        $this->assertEquals($countAfter, $countBefore - 1);
    }

    /**
     * @covers \Core\Contracts\ModelContract::getCount
     * @covers \Core\Contracts\ModelContract::deleteRows
     */
    public function testStaticDeleteRows()
    {
        $countBefore = User::getCount();
        User::deleteRows('age', 27, '>');
        $countAfter = User::getCount();

        $result = $this->getConnection()->getConnection()->query("SELECT * FROM {$this->quote('model_test_user')} WHERE `age` > 27")->fetchAll();
        $this->assertEmpty($result);
        $this->assertEquals($countAfter, $countBefore - 3);
    }

    /**
     * @covers \Core\Contracts\ModalContract::setTableName
     * @covers \Core\Contracts\ModalContract::setSoftDelete
     * @covers \Core\Contracts\ModalContract::findOne
     * @covers \Core\Contracts\ModalContract::softDelete
     */
    public function testSoftDelete()
    {
        User::setTableName('soft_delete_test_user');
        User::setSoftDelete(true);

        $user = User::findOne(['age' => 30]);
        $user->softDelete();
        $result = $this->getConnection()->getConnection()->query("SELECT * FROM {$this->quote('soft_delete_test_user')} WHERE `deleted_at` IS NOT NULL")->fetchAll();

        $this->assertEquals($result[0]['name'], $user->name);
    }

    /**
     * @covers \Core\Contracts\ModalContract::getFillable
     * @covers \Core\Contracts\ModalContract::$fillable
     */
    public function testModelFillableCriteriaIsMet()
    {
        $user = User::findOne(['age' => 30]);
        $fillable = User::getFillable();
        $userArr = (array) $user;
        foreach ($userArr as $key => $val) {
            $this->assertTrue(in_array($key, $fillable));
        }
    }

    public function testMove()
    {
        //TODO
    }

    public function testOneToOne()
    {
        $phone = User::findOne(['id' => 1])->phone();
        $this->assertInstanceOf('Core\Tests\Models\testModels\Phone',$phone);

        /** @var QueryBuilder $phoneObjs */
        $samsPhoneObj = User::findOne([new Where('age', 28, '>')])->phone();
        $this->assertInstanceOf('Core\Tests\Models\testModels\Phone', $samsPhoneObj);
    }

    public function testOneToOneInverse()
    {
        $user = Phone::findOne(['id' => 2])->user();
        $this->assertInstanceOf('Core\Tests\Models\testModels\User', $user);
    }

    public function testOneToMany()
    {
        $comments = Post::findOne(['id' => 1])->comments()->get();
        $this->assertInternalType('array', $comments);
        $this->assertInstanceOf('Core\Tests\Models\testModels\Comment', $comments[0]);

        $comment = Post::findOne(['id' => 1])->comments()->where('text', 'I LIKE!!!')->get(0);
        $this->assertInstanceOf('Core\Tests\Models\testModels\Comment', $comment);
    }

    public function testOneToManyInverse()
    {
        $post = Comment::findOne(['id' => 2])->post();
        $this->assertInstanceOf('Core\Tests\Models\testModels\Post', $post);
    }


    public function testManyToMany()
    {
        $qualifications = Employee::findOne(['id' => 1])->qualifications()->get();
        $this->assertInternalType('array', $qualifications);
    }
}
