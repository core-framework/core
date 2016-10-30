<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 23/04/16
 * Time: 1:29 AM
 */

namespace Core\Reactor;


class HeaderCollection extends DataCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected $headerNames = [];

    /**
     * Parameters constructor.
     * @param $parameters
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Add Key/Value pair to collection
     *
     * @param $key string
     * @param $value mixed
     */
    public function set($key, $value)
    {
        $_key = str_replace('_', '-', strtolower($key));
        $this->headerNames[$_key] = $key;
        parent::set($_key, $value);
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
        if (!is_null($key)) {
            $key = str_replace('_', '-', strtolower($key));
        }
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

    /**
     * @param $name
     * @return mixed
     */
    public function getOriginalName($name)
    {
        if (!isset($this->headerNames[$name])) {
            return implode('-', array_walk(explode('-', $name), 'strtoupper'));
        }

        return $this->headerNames[$name];
    }

}