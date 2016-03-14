<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 14/02/16
 * Time: 12:13 AM
 */

namespace Core\Tests\Models\testModels;

use Core\Model\Model;

class Post extends Model
{
    protected static $tableName = 'post';

    protected static $primaryKey = 'id';

    protected static $dbName = 'test';

    protected static $saveable = ['title', 'body'];

    protected static $fillable = ['id', 'title', 'body'];

    public function comments()
    {
        return $this->hasMany('Core\Tests\Models\testModels\Comment');
    }
}