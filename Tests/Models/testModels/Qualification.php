<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 23/02/16
 * Time: 10:44 AM
 */

namespace Core\Tests\Models\testModels;

use Core\Model\Model;

class Qualification extends Model
{
    protected static $tableName = 'qualification';

    protected static $primaryKey = 'id';

    protected static $dbName = 'test';

    protected static $saveable = ['qualification'];

    protected static $fillable = ['id', 'qualification'];

    public function employees()
    {
        return $this->belongsToMany('Core\Tests\Models\testModels\Employee');
    }
}