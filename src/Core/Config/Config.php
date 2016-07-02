<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 17/04/16
 * Time: 5:11 PM
 */

namespace Core\Config;

use Core\Contracts\Config as ConfigInterface;
use Core\Reactor\DataCollection;

class Config extends DataCollection implements ConfigInterface
{
    public function all()
    {
        return $this->get();
    }

    public function getServices()
    {
        return $this->get('app.services');
    }

    public function getDatabase()
    {
        return $this->get('database');
    }
}