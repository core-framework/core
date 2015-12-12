<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 17/12/14
 * Time: 12:31 AM
 */

namespace Core\Tests\CacheSystem;


use Core\Cache\AppCache;
use Core\Container\Container;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

class AppCacheTest extends \PHPUnit_Framework_TestCase
{
    public $cache;

    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('cache'));
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    /**
     * @covers \Core\CacheSystem\AppCache::cacheExists
     * @expectedException \ErrorException
     * @expectedExceptionMessage Cache Directory not defined!
     */
    public function testThrowsExceptionOnCacheExistsIfDirNotProvided()
    {
        AppCache::cacheExists('someKey');
    }

    /**
     * @covers \Core\CacheSystem\AppCache::cacheContent
     * @expectedException \ErrorException
     * @expectedExceptionMessage Cache Directory not defined!
     */
    public function testThrowsExceptionOnCacheContentIfDirNotProvided()
    {
        AppCache::cacheContent('SomeKey', 'SomeValue', 100);
    }

    /**
     * @covers \Core\CacheSystem\AppCache::getCache
     * @expectedException \ErrorException
     * @expectedExceptionMessage Cache Directory not defined!
     */
    public function testThrowsExceptionOnGetCacheIfDirNotProvided()
    {
        AppCache::getCache('SomeKey');
    }


    public function testDirIsGivenIsFalseWhenDirNotProvided()
    {
        $this->assertNotTrue(AppCache::$dirIsGiven);
    }

    /**
     * @covers \Core\CacheSystem\AppCache::__construct
     */
    public function testCacheConstruct()
    {
        $this->assertInstanceOf('\\Core\\Cache\\AppCache', new AppCache());
    }


    public function testIfDefaultDirIsSet()
    {
        AppCache::setCacheDir(vfsStream::url('cache'));
        $this->assertTrue(AppCache::$dirIsGiven);
    }

    /**
     * @covers \Core\CacheSystem\AppCache::getCache
     * @throws \ErrorException
     */
    public function testIfFalseIfNoCacheExists()
    {
        $this->assertNotTrue(AppCache::getCache('nonExistingCacheKey'));
    }

    /**
     * @covers \Core\CacheSystem\AppCache::cacheContent
     * @covers \Core\CacheSystem\AppCache::getCache
     * @throws \ErrorException
     */
    public function testIfStringContentIsCached()
    {
        AppCache::cacheContent('SomeCacheKey', 'Some long a** text', 200);

        $str = AppCache::getCache('SomeCacheKey');
        $this->assertEquals('Some long a** text', $str);
    }

    /**
     * @covers \Core\CacheSystem\AppCache::cacheContent
     * @covers \Core\CacheSystem\AppCache::getCache
     * @throws \ErrorException
     */
    public function testIfArrayContentIsCached()
    {
        $testArr = ['SomeArrKey' => 'SomeArrValue'];
        AppCache::cacheContent('SomeArrCacheKey', $testArr, 200);

        $arr = AppCache::getCache('SomeArrCacheKey');
        $this->assertEquals(json_encode($testArr), json_encode($arr));
    }

    /**
     * @covers \Core\CacheSystem\AppCache::cacheContent
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Object must implement CacheableContract interface
     */
    public function testIfThrowsExceptionWhenNonCacheableObjectIsCached()
    {
        $object = (object) array('property1' => 1, 'property2' => 'b');
        AppCache::cacheContent('SomeObjCacheKey', $object, 200);
    }

    /**
     * @covers \Core\CacheSystem\AppCache::cacheContent
     * @covers \Core\CacheSystem\AppCache::deleteCache
     * @throws \ErrorException
     */
    public function testIfCacheIsDeleted()
    {
        $testArr = ['SomeArrKey' => 'SomeArrValue'];
        AppCache::cacheContent('SomeArrCacheKey', $testArr, 200);
        AppCache::cacheContent('SomeCacheKey', 'Some long a** text', 200);
        $this->assertTrue(AppCache::deleteCache('SomeArrCacheKey'));
        $this->assertTrue(AppCache::deleteCache('SomeCacheKey'));
    }
}
