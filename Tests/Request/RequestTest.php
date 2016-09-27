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

namespace Core\Tests\Request;

use Core\Reactor\DataCollection;
use Core\Request\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var $request Request
     */
    public $request;

    public function setUp()
    {
        $this->request = Request::createFromGlobals();
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testRequest()
    {
        $this->assertInstanceOf('\Core\Request\Request', $this->request);
    }

    public function testGetIp()
    {
        $this->assertSame('127.0.0.1', $this->request->ip());
    }

    public function testGetSetPath()
    {
        $this->assertSame('/', $this->request->getPath());
        $this->request->setPath('/testing/path');
        $this->assertEquals('/testing/path', $this->request->getPath());
    }

    public function testDataCollections()
    {
        $this->assertInstanceOf(DataCollection::class, $this->request->GET);
        $this->assertInstanceOf(DataCollection::class, $this->request->POST);
        $this->assertInstanceOf(DataCollection::class, $this->request->server);
        $this->assertInstanceOf(DataCollection::class, $this->request->headers);
        $this->assertInstanceOf(DataCollection::class, $this->request->cookies);
    }

//    public function testQueryStringNotInPath()
//    {
//        $this->request->setPath('/testing/path?key=value');
//        $this->assertEquals('/testing/path', $this->request->getPath());
//    }

}
