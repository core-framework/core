<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 23/02/16
 * Time: 10:36 AM
 */

namespace Core\Tests\Models\testModels;


use Core\Model\Model;

class Employee extends Model
{
    protected static $tableName = 'employee';

    protected static $primaryKey = 'id';

    protected static $dbName = 'test';

    protected static $saveable = ['first_name', 'last_name', 'job_title', 'salary'];

    protected static $fillable = ['id', 'first_name', 'last_name', 'job_title', 'salary'];

    public function qualifications()
    {
        return $this->belongsToMany('Core\Tests\Models\testModels\Qualification');
    }
}