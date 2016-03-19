<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 16/03/16
 * Time: 11:30 AM
 */

namespace Core\Contracts;


interface ConfigContract
{
    /**
     * @param null $confKey
     * @return array|bool|mixed|null
     */
    public static function get($confKey = null);

    /**
     * @return array|bool|mixed|null
     */
    public static function getDatabase();

    /**
     * @return array|bool|mixed|null
     */
    public static function getGlobal();

    /**
     * @return array|bool|mixed|null
     */
    public static function getRoutes();

    /**
     * @return array|bool|mixed|null
     */
    public static function getEnvironment();

    /**
     * @return array|bool|mixed|null
     */
    public static function getServices();

    /**
     * @param $name
     * @param array $confArr
     * @return mixed
     */
    public static function add($name, array $confArr);

    /**
     * @param array $confArr
     * @return mixed
     */
    public static function set(array $confArr);

    /**
     * @return string
     */
    public static function getConfDir();

    /**
     * @param string $confDir
     */
    public static function setConfDir($confDir);
}