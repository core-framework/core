<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 26/01/16
 * Time: 7:27 PM
 */

namespace Core\Tests\Models\testModels;

use Core\Model\Model;

class User extends Model
{
    protected static $tableName = 'model_test_user';

    protected static $primaryKey = 'id';

    protected static $dbName = 'test';

    protected static $saveable = ['fname', 'lname', 'name', 'age'];

    protected static $fillable = ['id', 'fname', 'lname', 'name', 'age', 'created_at'];


    public function phone()
    {
        return $this->hasOne('Core\Tests\Models\testModels\Phone');
    }

}