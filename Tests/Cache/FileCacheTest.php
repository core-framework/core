<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This file is part of the Core Framework package.
 *
 * (c) Shalom Sam <shalom.s@coreframework.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Tests\Cache;


use Core\Application\Application;
use Core\Facades\Cache;
use Core\Container\Container;
use Core\FileSystem\FileSystem;
use org\bovigo\vfs\vfsStream;

class FileCacheTest extends \PHPUnit_Framework_TestCase
{
    public $stream;
    public static $structure = [
        'cache' => []
    ];

    public function setUp()
    {
        $this->stream = vfsStream::setup('root', 0777, self::$structure);
        Application::register('FileSystem', \Core\FileSystem\FileSystem::class);
        Application::register('Cache', \Core\Cache\FileCache::class)->setArguments(['FileSystem', vfsStream::url('root/cache')]);
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    /**
     * @covers \Core\Cache\FileCache::put
     * @covers \Core\Cache\FileCache::get
     */
    public function testCachePutAndGet()
    {
        $this->assertTrue(Cache::put('someKey', 'someStringValue', 600));
        $this->assertEquals(Cache::get('someKey'), 'someStringValue');

        $this->assertTrue(Cache::put('someArrKey', ['test1', 'test2', 'test3'], 600));
        $this->assertEquals(Cache::get('someArrKey'), ['test1', 'test2', 'test3']);

        $obj = new \stdClass();
        $obj->setVar = 'someValue';
        $this->assertTrue(Cache::put('someObjKey', $obj, 600));
        $this->assertInstanceOf('stdClass', Cache::get('someObjKey'));
        $this->assertObjectHasAttribute('setVar', Cache::get('someObjKey'));
    }

    /**
     * @covers \Core\Cache\FileCache::get
     */
    public function testCacheExpiredReturnsFalse()
    {
        Cache::put('someCacheToExpire', 'someCacheValue', 1);
        sleep(1);
        $this->assertFalse(Cache::get('someCacheToExpire'));
    }

    /**
     * @covers \Core\Cache\FileCache::exists
     * @covers \Core\Cache\FileCache::put
     */
    public function testCacheExists()
    {
        $this->assertFalse(Cache::exists('someCacheToExpire'));
        Cache::put('someNewKey', 'testString', 60);
        $this->assertTrue(Cache::exists('someNewKey'));
    }

    /**
     * @covers \Core\Cache\FileCache::delete
     */
    public function testCacheDelete()
    {
        Cache::put('someNewKey', 'testString', 60);
        $this->assertTrue(Cache::delete('someNewKey'));
        $this->assertFalse(FileSystem::exists(vfsStream::url('root/cache/' . md5('someNewKey') . '.php')));
    }

    /**
     * @covers \Core\Cache\FileCache::destroy
     */
    public function testCacheDestroy()
    {
        $this->assertInternalType('array', Cache::destroy());
        /* Check if Cache Directory is empty */
        $this->assertEquals(count(scandir(vfsStream::url('root/cache'))), 2);
    }
}
