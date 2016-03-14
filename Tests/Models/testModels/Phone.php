<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 14/02/16
 * Time: 12:13 AM
 */

namespace Core\Tests\Models\testModels;

use Core\Model\Model;

class Phone extends Model
{
    protected static $tableName = 'phone';

    protected static $primaryKey = 'id';

    protected static $dbName = 'test';

    protected static $saveable = ['user_id', 'phone'];

    protected static $fillable = ['id', 'user_id', 'phone'];

    public function user()
    {
        return $this->belongsTo('Core\Tests\Models\testModels\User');
    }
}