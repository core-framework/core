<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 24/01/16
 * Time: 2:18 AM
 */

namespace Core\Tests\Router;


use Core\Router\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{

    public function up()
    {

    }

    public function down()
    {

    }

    public function testRouteGet()
    {
        $handler = function() {
            echo 'hello world';
        };
        $router = new Router();
        $router->get('/test/uri', $handler);
        $collection = $router->getCollection();
        $this->assertArrayHasKey('/test/uri', $collection);
        $this->assertInstanceOf('\Core\Router\Route', $collection['/test/uri']);
        $this->assertEquals('/test/uri', $collection['/test/uri']->getUri());
        $this->assertInstanceOf('\Closure', $collection['/test/uri']->getAction());

    }
}
