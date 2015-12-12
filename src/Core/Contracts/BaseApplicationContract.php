<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 14/11/15
 * Time: 1:00 AM
 */

namespace Core\Contracts;


interface BaseApplicationContract
{
    /**
     * Get Application Version
     *
     * @return string
     */
    public function version();

    /**
     * Get Application Base Path
     *
     * @return string
     */
    public function basePath();

    /**
     * Get current Environment state
     *
     * @return string
     */
    public function environment();

    /**
     * Check if Application is Down
     *
     * @return bool
     */
    public function isDown();

    /**
     * load Base Components required by Application.
     *
     * @return void
     */
    public function loadBaseComponents();

    /**
     * Method to register services
     *
     * @param $name
     * @param $definition
     * @param bool $shared
     * @return mixed
     * @throws \ErrorException
     */
    public static function register($name, $definition, $shared = true);


    /**
     * Boot Application Services
     *
     * @return void
     */
    public function boot();


    /**
     * Get path to "framework.conf.php" file
     *
     * @return mixed
     */
    public function getConfigPath();

}