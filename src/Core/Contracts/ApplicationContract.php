<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 23/11/15
 * Time: 10:51 PM
 */

namespace Core\Contracts;


interface ApplicationContract extends BaseApplicationContract
{
    /**
     * Run Application
     *
     * @return mixed
     */
    public function run();
}