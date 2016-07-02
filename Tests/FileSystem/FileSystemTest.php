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


namespace Core\Tests\FileSystem;


use Core\Application\Application;
use Core\Container\Container;
use Core\Facades\FileSystem;
use org\bovigo\vfs\vfsStream;

class FileSystemTest extends \PHPUnit_Framework_TestCase
{
    public $stream;

    public static $structure = [
        'dir1' => [
            'subDir1' => [
                'subFile1' => "subFile1 contents",
                'subFile2' => "subFile2 contents",
            ],
            'subDir2' => [
                'sub2File1' => "sub2File1 contents",
                'sub2File2' => "sub2File2 contents",
            ]
        ],
        'dir2' => [
            'file1' => "file1 contents",
            'file2' => "file2 contents",
            'file3' => "file3 content",
            'file4.txt' => "file4.txt content"
        ]
    ];

    public function setUp()
    {
        $this->stream = vfsStream::setup('root', 0777, self::$structure);
        Application::register('FileSystem', \Core\FileSystem\FileSystem::class);
        parent::setUp();
    }

    public function tearDown()
    {
        Container::reset();
        parent::tearDown();
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::read
     */
    public function testFileRead()
    {
        $r = FileSystem::read(vfsStream::url('root/dir1/subDir1/subFile1'));
        $r2 = FileSystem::read(vfsStream::url('root/dir1/subDir2/sub2File2'));
        $this->assertEquals("subFile1 contents", $r);
        $this->assertEquals("sub2File2 contents", $r2);
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::getContents
     */
    public function testFileGetContents()
    {
        $this->assertEquals("file1 contents", FileSystem::getContents(vfsStream::url('root/dir2/file1')));
        $this->assertEquals("file2 contents", FileSystem::getContents(vfsStream::url('root/dir2/file2')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::write
     */
    public function testFileWrite()
    {
        $this->assertTrue(FileSystem::write(vfsStream::url('root/dir2/file3'), 'file3 contents'));
        $this->assertEquals("file3 contents", FileSystem::getContents(vfsStream::url('root/dir2/file3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::prepend
     */
    public function testFilePrepend()
    {
        $this->assertTrue(FileSystem::prepend(vfsStream::url('root/dir2/file3'), 'prepend content '));
        $this->assertEquals('prepend content file3 content', FileSystem::getContents(vfsStream::url('root/dir2/file3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::append
     */
    public function testFileAppend()
    {
        $this->assertTrue(FileSystem::append(vfsStream::url('root/dir2/file3'), ' appended content'));
        $this->assertEquals('file3 content appended content', FileSystem::getContents(vfsStream::url('root/dir2/file3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::exists
     */
    public function testFileExists()
    {
        $this->assertTrue(FileSystem::exists(vfsStream::url('root/dir1/subDir1/subFile1')));
        $this->assertTrue(FileSystem::exists(vfsStream::url('root/dir1/subDir1/subFile2')));
        $this->assertFalse(FileSystem::exists(vfsStream::url('root/dir1/subDir1/subFile3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::delete
     */
    public function testFileDelete()
    {
        $this->assertTrue(FileSystem::delete(vfsStream::url('root/dir2/file3')));
        $this->assertFalse(FileSystem::exists(vfsStream::url('root/dir2/file3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::move
     */
    public function testFileMove()
    {
        $this->assertTrue(FileSystem::move(vfsStream::url('root/dir2/file3'), vfsStream::url('root/dir1/file3')));
        $this->assertFalse(FileSystem::exists(vfsStream::url('root/dir2/file3')));
        $this->assertTrue(FileSystem::exists(vfsStream::url('root/dir1/file3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::copy
     */
    public function testFileCopy()
    {
        $this->assertTrue(FileSystem::copy(vfsStream::url('root/dir2/file1'), vfsStream::url('root/dir1/file1copy')));
        $this->assertTrue(FileSystem::exists(vfsStream::url('root/dir2/file1')));
        $this->assertTrue(FileSystem::exists(vfsStream::url('root/dir1/file1copy')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::mkdir
     */
    public function testMakeDirectory()
    {
        $this->assertTrue(FileSystem::mkdir(vfsStream::url('root/newDir')));
        $this->assertTrue(is_dir(vfsStream::url('root/newDir')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::pathInfo
     * @covers \Core\FileSystem\FileSystemKernel::basename
     * @covers \Core\FileSystem\FileSystemKernel::dirname
     * @covers \Core\FileSystem\FileSystemKernel::filename
     * @covers \Core\FileSystem\FileSystemKernel::extension
     */
    public function testFilePathInfo()
    {
        $this->assertTrue(FileSystem::exists(vfsStream::url('root/dir2/file3')));
        $pathInfo = FileSystem::pathInfo(vfsStream::url('root/dir2/file3'));
        $this->assertInternalType('array', $pathInfo);
        $this->assertArrayHasKey('basename', $pathInfo);
        $this->assertArrayHasKey('dirname', $pathInfo);
        $this->assertArrayHasKey('filename', $pathInfo);

        $this->assertInternalType('string',FileSystem::basename(vfsStream::url('root/dir2/file3')));
        $this->assertEquals('file3', FileSystem::basename(vfsStream::url('root/dir2/file3')));

        $this->assertInternalType('string',FileSystem::dirname(vfsStream::url('root/dir2/file3')));
        $this->assertEquals('vfs://root/dir2', FileSystem::dirname(vfsStream::url('root/dir2/file3')));

        $this->assertInternalType('string',FileSystem::filename(vfsStream::url('root/dir2/file3')));
        $this->assertEquals('file3', FileSystem::filename(vfsStream::url('root/dir2/file3')));

        $this->assertInternalType('string',FileSystem::extension(vfsStream::url('root/dir2/file3')));
        $this->assertEquals('', FileSystem::extension(vfsStream::url('root/dir2/file3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::size
     */
    public function testFileSize()
    {
        $this->assertInternalType('integer', FileSystem::size(vfsStream::url('root/dir2/file3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::lastModified
     */
    public function testFileModifiedTime()
    {
        $this->assertInternalType('integer', FileSystem::lastModified(vfsStream::url('root/dir2/file3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::isDir
     */
    public function testIsDir()
    {
        $this->assertTrue(FileSystem::isDir(vfsStream::url('root/dir2')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::isFile
     */
    public function testIsFile()
    {
        $this->assertTrue(FileSystem::isFile(vfsStream::url('root/dir2/file3')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::type
     */
    public function testFileType()
    {
        $this->assertSame('file', FileSystem::type(vfsStream::url('root/dir2/file4.txt')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::mime
     */
    public function testFileMime()
    {
        $this->assertEquals('text/plain', FileSystem::mime(vfsStream::url('root/dir2/file4.txt')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::isWritable
     */
    public function testFileIsWritable()
    {
        $this->assertTrue(FileSystem::isWritable(vfsStream::url('root/dir2/file4.txt')));
    }

    /**
     * @covers \Core\FileSystem\FileSystemKernel::find
     */
    public function testFileFind()
    {
        /*
         * Glob doesn't work with vfsStream
         */
        $this->assertCount(2, FileSystem::find('*.php', __DIR__));
        $this->assertContains('ExplorerTest.php', FileSystem::find('*.php', __DIR__)[0]);
        $this->assertContains('FileSystemTest.php', FileSystem::find('*.php', __DIR__)[1]);
    }
    
}
