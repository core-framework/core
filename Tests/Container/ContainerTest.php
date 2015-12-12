<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 22/11/14
 * Time: 8:47 AM
 */

namespace Core\Tests\Container;

use Core\Container\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::get
     * @throws \ErrorException
     */
    public function testReferenceMatch()
    {
        $Container = new Container();
        $Container->register('_Container', $Container);
        $Container->register('Smarty', '\\Smarty');
        $Container->register('View', '\\Core\\Views\\AppView')
            ->setArguments(array(null, 'Smarty'));

        $a = $Container->get('View');
        $a->set('showHeader', false);
        $b = $Container->get('View');
        $b->set('showFooter', false);
        $c = $Container->get('View');

        $this->assertEquals($a, $c);
        $this->assertEquals($b, $c);
    }

    /**
     * @covers \Core\Container\Container::register
     * @covers \Core\Container\Container::get
     * @throws \ErrorException
     */
    public function testCanRegisterClass()
    {
        $Container = new Container();
        $Container->register('Cache', \Core\Cache\OPCache::class);

        $cache = $Container->get('Cache');

        $this->assertInstanceOf('\\Core\\Cache\\OPCache', $cache);
    }

} 
