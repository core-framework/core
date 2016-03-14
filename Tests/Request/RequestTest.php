<?php


namespace Test\Request\Request;


use Core\Request\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    public function up()
    {

    }

    public function down()
    {

    }

    public function testRequest()
    {
        $request = new Request(['test' => 'val']);
        $this->assertArrayHasKey('test', $request->config);
    }
}
