<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 02/11/15
 * Time: 1:25 AM
 */

namespace Core\Tests\Application;

use Core\Application\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    /**
     * @covers \Core\Application\BaseApplication::__construct
     */
    public function testConstruct()
    {
        $app = new Application();
        $this->assertInstanceOf('\\Core\\Application\\Application', $app);
    }
}
