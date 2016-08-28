<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 23/04/16
 * Time: 1:29 AM
 */

namespace Core\Reactor;


class HttpParameter extends DataCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected static $preserveOriginal = false;

    /**
     * Parameters constructor.
     * @param $parameters
     */
    public function __construct(array $parameters)
    {
        array_walk($parameters, function($value, $key) use ($parameters) {
            $parameters[str_replace('_', '-', strtolower($key))] = $value;
            
            if (!static::$preserveOriginal) {
                unset($parameters[$key]);
            }
        });
        parent::__construct($parameters);
    }

    /**
     * Preserve Original Values in Collection
     */
    public static function preserveOriginal()
    {
        static::$preserveOriginal = true;
    }

    /**
     * Add Key/Value pair to collection
     *
     * @param $key string
     * @param $value mixed
     */
    public function set($key, $value)
    {
        $key = str_replace('_', '-', strtolower($key));
        parent::set($key, $value);
    }

    /**
     * Find Key value in collection, returns default on failure
     *
     * @param null $key Key to be searched
     * @param bool $default Default value to return on failure
     * @return array|bool|mixed
     */
    public function get($key = null, $default = false)
    {
        $key = str_replace('_', '-', strtolower($key));
        return parent::get($key, $default);
    }

    /**
     * Checks if Key exists in collection
     *
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        $key = str_replace('_', '-', strtolower($key));
        return parent::has($key);
    }

    
    
}