<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 14/01/16
 * Time: 12:47 AM
 */

namespace Core\Database\Language;


use Core\Contracts\Database\LanguageContract;
use Core\Database\Connection;

abstract class BaseLanguage implements LanguageContract
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * BaseLanguage constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return Connection
     */
    public abstract function getConnection();

    /**
     * @param Connection $connection
     * @return LanguageContract
     */
    public abstract function setConnection(Connection $connection);
}