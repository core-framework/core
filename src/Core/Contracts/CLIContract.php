<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 23/11/15
 * Time: 10:54 PM
 */

namespace Core\Contracts;


interface CLIContract extends BaseApplicationContract
{
    /**
     * Parse Command Line
     *
     * @return mixed
     */
    public function parse();
}