<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 14/02/16
 * Time: 12:13 AM
 */

namespace Core\Tests\Models\testModels;

use Core\Model\Model;

class Comment extends Model
{
    protected static $tableName = 'comment';

    protected static $primaryKey = 'id';

    protected static $dbName = 'test';

    protected static $saveable = ['post_id', 'body'];

    protected static $fillable = ['id', 'post_id', 'body'];

    public function post()
    {
        return $this->belongsTo('Core\Tests\Models\testModels\Post');
    }
}