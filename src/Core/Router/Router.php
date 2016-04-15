<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 14/04/16
 * Time: 9:25 AM
 */

namespace Core\Router;

use Core\Application\Application;

class Router extends RouterKernel
{
    private static $routerKernel;

    public static function __callStatic($name, $arguments)
    {
        $instance = static::kernel();

        if (!method_exists($instance, $name)) {
            throw new \LogicException(get_called_class() . " does not implement " . $name . " method.");
        }

        return call_user_func_array([$instance, $name], $arguments);
    }

    protected static function kernel()
    {
        if (!static::$routerKernel) {
            self::$routerKernel = Application::get('Router');
        }
        return self::$routerKernel;
    }
}