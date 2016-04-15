<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 19/03/16
 * Time: 3:12 PM
 */

namespace Core\Contracts;

use Core\Router\Router;

interface MiddlewareContract
{
    public function run(Router $router, \Closure $next);
}