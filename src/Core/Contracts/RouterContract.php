<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 16/11/15
 * Time: 12:01 AM
 */

namespace Core\Contracts;


interface RouterContract
{
    /**
     * Set Config for the Router
     *
     * @param array $config
     * @return mixed
     */
    public function setConfig(array $config);

    /**
     * Method to resolve the current Route
     *
     * @param bool|false $useAestheticRouting
     * @return mixed
     */
    public function resolve($useAestheticRouting = false);

    /**
     * Method to check if URL (path) matches defined paths (route Conf file)
     *
     * @return void
     */
    public function checkIfPatternMatch();
}