<?php

use Core\Container\Container;

$di = new Container();
$di->register('Cache', '\\Core\\Cache\\AppCache');
$di->register('Config', '\\Core\\Config\\AppConfig');
$di->register('IOStream', '\\Core\\Console\\IOStream');
$di->register('CLI', "\\Core\\Console\\CLI")
    ->setArguments(array('IOStream', 'Config'));
$di->register('Core', "\\Core\\Console\\Core")
    ->setArguments(array('IOStream', 'Config'));