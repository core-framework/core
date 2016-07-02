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

namespace Core\Database;


use Core\Contracts\Database\Mapper;

class Table
{
    protected $name;

    protected $options;

    /**
     * @var array|Column[] of Column(s)
     */
    protected $columns = [];

    protected $foreignKeys = [];

    protected $primaryKeys = [];

    protected $data = [];

    protected $mapper;

    /**
     * Table constructor.
     * @param $name
     * @param $options
     * @param $mapper Mapper
     */
    public function __construct($name, array $options = [], Mapper $mapper = null)
    {
        $this->setName($name);
        if (!empty($options)) {
            $this->setOptions($options);
        }
        if (!is_null($mapper)) {
            $this->setMapper($mapper);
        }
        return $this;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param Column $column
     * @return $this
     */
    public function setColumn(Column $column)
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param $mapper
     * @return $this
     */
    public function setMapper($mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * @return array
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * @return array
     */
    public function getForeignKeyNames()
    {
        $foreignKeyNames = [];
        if (empty($this->foreignKeys)) {
            /** @var Column $column */
            foreach($this->getColumns() as $column) {
                if ($column->isForeignKey()) {
                    $foreignKeyNames[] = $column->getName();
                    $this->foreignKeys[] = $column;
                }
            }
        } else {
            /** @var Column $column */
            foreach($this->foreignKeys as $column) {
                if ($column->isForeignKey()) {
                    $foreignKeyNames[] = $column->getName();
                }
            }
        }

        return $foreignKeyNames;
    }

    /**
     * @param $foreignKeys
     * @return $this
     */
    public function setForeignKeys(array $foreignKeys)
    {
        $this->foreignKeys = $foreignKeys;

        return $this;
    }

    /**
     * @return array
     */
    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }

    /**
     * @return array
     */
    public function getPrimaryKeyNames()
    {
        $primaryKeyNames = [];
        if (empty($this->primaryKeys)) {
            /** @var Column $column */
            foreach($this->getColumns() as $column) {
                if ($column->isPrimaryKey()) {
                    $primaryKeyNames[] = $column->getName();
                    $this->primaryKeys[] = $column;
                }
            }
        } else {
            /** @var Column $column */
            foreach($this->primaryKeys as $column) {
                if ($column->isPrimaryKey()) {
                    $primaryKeyNames[] = $column->getName();
                }
            }
        }

        return $primaryKeyNames;
    }

    /**
     * @param $primaryKeys
     * @return $this
     */
    public function setPrimaryKeys(array $primaryKeys)
    {
        $this->primaryKeys = $primaryKeys;

        return $this;
    }

    /**
     * @param Column $column
     * @return $this
     */
    public function addPrimaryKey(Column $column)
    {
        $this->primaryKeys[] = $column;

        return $this;
    }

    /**
     * @param $columns
     * @param $referencedTable
     * @param array $referencedColumns
     * @param array $options
     * @return $this
     */
    public function addForeignKey($columns, $referencedTable, $referencedColumns = ['id'], $options = [] )
    {
        $foreignKey = new ForeignKey();

        if (!is_array($referencedColumns)) {
            $referencedColumns = array($referencedColumns);
        }
        $foreignKey->setReferenceColumns($referencedColumns);

        if ($referencedTable instanceof Table) {
            $foreignKey->setReferenceTable($referencedTable);
        } else {
            $foreignKey->setReferenceTable(new Table($referencedTable, [], $this->mapper));
        }

        $foreignKey->setColumns($columns);
        $foreignKey->setOptions($options);

        $this->foreignKeys[] = $foreignKey;

        return $this;
    }


    /**
     * @param array $columns Column Names
     * @param $data array of data of the form:
     *     array(
     *          array("value1", "value2",.....),
     *          array("value1", "value2",.....),
     *          .......
     *     );
     * @return $this
     */
    public function insert(array $columns, $data)
    {
        foreach($columns as $column) {

            if (!$column instanceof Column) {
                $columnObj = new Column();
                $columnObj->setName($column);
                $column = $columnObj;
            }

            $this->setColumn($column);
        }

        $this->setData($data);

        return $this;
    }

    /**
     * @param $columnName
     * @param $type
     * @param array $options
     * @return $this
     */
    public function addColumn($columnName, $type = null, $options = [])
    {
        if (!$columnName instanceof Column) {
            $column = new Column();
            $column->setName($columnName)->setDataType($type);
            if(!empty($options)) {
                $column->setOptions($options);

                if (isset($options['primaryKey'])) {
                    $this->addPrimaryKey($column);
                }

                if (isset($options['foreignKey']) && isset($options['referencedTable']) && isset($options['referencedColumns'])) {
                    $this->addForeignKey($column, $options['referencedTable'], $options['referencedColumns']);
                }
            }
        } else {
            $column = $columnName;
        }

        $this->setColumn($column);

        return $this;
    }

    public function getTableOptionsStr()
    {
        $sql = "";
        $defaultTableOptions = ['engine' => 'InnoDB', 'charset' => 'utf8', 'collate' => 'utf8_unicode_ci'];
        $tableOptions = $this->getOptions();

        $sql .= isset($tableOptions['engine']) === true ? " ENGINE=" . $tableOptions['engine'] : " ENGINE=" . $defaultTableOptions['engine'];
        $sql .= isset($tableOptions['charset']) === true ? " CHARACTER SET " . $tableOptions['charset'] : " CHARACTER SET ". $defaultTableOptions['charset'];
        $sql .= isset($tableOptions['collate']) === true ? " COLLATE " . $tableOptions['collate'] : " COLLATE " . $defaultTableOptions['collate'];

        if (isset($tableOptions['comment'])) {
            $sql .= " COMMENT={$tableOptions['comment']}";
        }

        return $sql;
    }

    /**
     * Adds 'created_at' and 'modified_at' columns to table
     */
    public function addTimestamps()
    {
        $this->addColumn(Mapper::CREATED_AT, 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
            ->addColumn(Mapper::MODIFIED_AT, 'timestamp', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'));

        return $this;
    }

    /**
     * Adds 'deleted_at' column to table
     */
    public function addDelete()
    {
        $this->addColumn(Mapper::DELETED_AT, 'timestamp', array('default' => null, 'null' => true));

        return $this;
    }

    public function create()
    {

    }

    public function save()
    {

    }

    public function update()
    {

    }
}