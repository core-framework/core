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

    /**
     * @covers \Core\Request\Request::__construct
     */
    public function testRequest()
    {
        $this->assertInstanceOf('\Core\Request\Request', $this->request);
    }

    /**
     * @covers \Core\Request\Request::ip
     */
    public function testGetIp()
    {
        $this->assertSame('127.0.0.1', $this->request->ip());
    }

    /**
     * @covers \Core\Request\Request::setPath
     * @covers \Core\Request\Request::getPath
     */
    public function testGetSetPath()
    {
        $this->assertSame('/', $this->request->getPath());
        $this->request->setPath('/testing/path');
        $this->assertEquals('/testing/path', $this->request->getPath());
    }

    /**
     * @covers \Core\Request\Request
     */
    public function testDataCollections()
    {
        $this->assertInstanceOf(DataCollection::class, $this->request->GET);
        $this->assertInstanceOf(DataCollection::class, $this->request->POST);
        $this->assertInstanceOf(DataCollection::class, $this->request->server);
        $this->assertInstanceOf(DataCollection::class, $this->request->headers);
        $this->assertInstanceOf(DataCollection::class, $this->request->cookies);
    }

    /**
     * @covers \Core\Request\Request::setPath
     * @covers \Core\Request\Request::getPath
     * @covers \Core\Request\Request::getQueryString
     */
    public function testQueryStringNotInPath()
    {
        $this->request->setPath('/testing/path?key=value');
        $this->assertEquals('/testing/path', $this->request->getPath());
        $this->assertEquals('key=value', $this->request->getQueryString());
    }

    /**
     * @covers \Core\Request\Request::getHttpMethod
     */
    public function testHTTPMethod()
    {
        $request = Request::create('', 'GET');
        $this->assertSame('GET', $request->getHttpMethod());

        $request = Request::create('', 'POST');
        $this->assertSame('POST', $request->getHttpMethod());

        $request = Request::create('', 'PUT');
        $this->assertSame('PUT', $request->getHttpMethod());

        $request = Request::create('', 'DELETE');
        $this->assertSame('DELETE', $request->getHttpMethod());

        $request = Request::create('', 'OPTIONS');
        $this->assertSame('OPTIONS', $request->getHttpMethod());

        $request = Request::create('', 'PATCH');
        $this->assertSame('PATCH', $request->getHttpMethod());

        $request = Request::create('', 'HEAD');
        $this->assertSame('HEAD', $request->getHttpMethod());
    }

    /**
     * @covers \Core\Request\Request::body
     */
    public function testBodyMethod()
    {
        $request = Request::create('http://example.com/foo/bar', 'GET', [], [], [], [], 'Hello World');
        $this->assertSame('Hello World', $request->body());
    }

    /**
     * @covers \Core\Request\Request::isSecure
     */
    public function testIsSecureMethod()
    {
        $request = Request::create('https://example.com/foo/bar');
        $this->assertTrue($request->isSecure());
    }

    /**
     * @covers \Core\Request\Request::getScheme
     */
    public function testGetSchemeMethod()
    {
        $request = Request::create('https://example.com/foo/bar');
        $this->assertSame('https', $request->getScheme());
    }

    /**
     * @covers \Core\Request\Request::getHost
     */
    public function testGetHostMethod()
    {
        $request = Request::create('http://example.com/foo/bar');
        $this->assertSame('example.com', $request->getHost());
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetHostMethodException()
    {
        $request = Request::create('http://asd??sdq.com/foo/bar');
        $request->getHost();
    }

    /**
     * @covers \Core\Request\Request::ip
     */
    public function testIpMethod()
    {
        $request = Request::create();
        $this->assertSame('127.0.0.1', $request->ip());
    }

    /**
     * @covers \Core\Request\Request::userAgent
     */
    public function testUserAgentMethod()
    {
        $request = Request::create();
        $this->assertSame('CoreFramework/X.X.X', $request->userAgent());
    }

    /**
     * @covers \Core\Request\Request::isAjax
     */
    public function testIsAjaxMethod()
    {
        $request = Request::create('/', 'GET',[], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $this->assertTrue($request->isAjax());

        $request2 = Request::create('/');
        $this->assertFalse($request2->isAjax());

        $request3 = Request::create('/', 'POST', [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $this->assertTrue($request3->isAjax());
    }

    /**
     * @covers \Core\Request\Request::server
     */
    public function testServerMethod()
    {
        $request = Request::create();
        $this->assertSame('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', $request->server('HTTP_ACCEPT'));
    }

    /**
     * @covers \Core\Request\Request::getRequestUri
     */
    public function testGetRequestUriMethod()
    {
        $request = Request::create('http://example.com/somePath', 'GET', [], ['HTTP_X_ORIGINAL_URL' => '/someOtherPath']);
        $this->assertSame('/someOtherPath', $request->getRequestUri());

        $request2 = Request::create('http://example.com/somePath', 'GET', [], ['HTTP_X_REWRITE_URL' => '/someOtherPath']);
        $this->assertSame('/someOtherPath', $request2->getRequestUri());

        $request3 = Request::create('http://example.com/somePath', 'GET', [], ['IIS_WasUrlRewritten' => '1', 'UNENCODED_URL' => '/someOtherPath']);
        $this->assertSame('/someOtherPath', $request3->getRequestUri());

        $request4 = Request::create('http://example.com/somePath', 'GET', [], ['IIS_WasUrlRewritten' => '1', 'UNENCODED_URL' => '/someOtherPath']);
        $this->assertSame('/someOtherPath', $request4->getRequestUri());

        $request5 = Request::create('http://example.com/somePath');
        $this->assertSame('/somePath', $request5->getRequestUri());
    }

    /**
     * @covers \Core\Request\Request::getSchemeAndHost
     */
    public function testGetSchemeAndHostMethod()
    {
        $request = Request::create('http://example.com/somePath');
        $this->assertSame('http://example.com', $request->getSchemeAndHost());
    }

    /**
     * @covers \Core\Request\Request::body
     */
    public function testGetBodyMethod()
    {
        $request = Request::create('http://example.com/somePath', 'GET', [], [], [], [], 'Hello World');
        $this->assertSame('Hello World', $request->body());
    }

    /**
     * @covers \Core\Request\Request::input
     * @covers \Core\Request\Request::POST
     * @covers \Core\Request\Request::PUT
     * @covers \Core\Request\Request::GET
     */
    public function testInputMethod()
    {
        $request = Request::create('http://example.com/somePath?key=value', 'GET');
        $this->assertSame('value', $request->GET('key'));
        $this->assertSame('value', $request->input('key'));

        $request2 = Request::create('http://example.com/somePath?key=value', 'POST', ['dataKey' => 'dataValue']);
        $this->assertSame('value', $request2->GET('key'));
        $this->assertSame('value', $request2->input('key'));
        $this->assertSame('dataValue', $request2->POST('dataKey'));
        $this->assertSame('dataValue', $request2->input('dataKey'));

        $request3 = Request::create('http://example.com/somePath?key=value', 'PUT', ['dataKey' => 'dataValue']);
        $this->assertSame('value', $request3->GET('key'));
        $this->assertSame('value', $request3->input('key'));
        $this->assertSame('dataValue', $request3->PUT('dataKey'));
        $this->assertSame('dataValue', $request3->input('dataKey'));

        $request4 = Request::create('http://example.com/somePath?key=value', 'DELETE', ['dataKey' => 'dataValue']);
        $this->assertSame('value', $request4->GET('key'));
        $this->assertSame('value', $request4->input('key'));
        $this->assertSame('dataValue', $request4->DELETE('dataKey'));
        $this->assertSame('dataValue', $request4->input('dataKey'));

    }

    /**
     * @covers \Core\Request\Request::cookies
     */
    public function testCookiesMethod()
    {
        $request = Request::create('http://example.com/somePath?key=value', 'GET', [], [], ['test' => 'value']);
        $this->assertSame('value', $request->cookies('test'));
    }

    /**
     * @covers \Core\Request\Request::headers
     */
    public function testHeadersMethod()
    {
        $request = Request::create();
        $headers = $request->headers()->get();
        $this->assertInternalType('array', $headers);
        $this->assertArrayHasKey('host', $headers);
        $this->assertArrayHasKey('user-agent', $headers);
        $this->assertArrayHasKey('accept', $headers);
        $this->assertArrayHasKey('accept-language', $headers);
        $this->assertArrayHasKey('accept-charset', $headers);
    }

    /**
     * @covers \Core\Request\Request::json
     * @covers \Core\Request\Request::isJson
     */
    public function testJsonAndIsJsonMethod()
    {
        $request = Request::create('http://example.com/somePath', 'GET', [], ['CONTENT_TYPE' => 'application/json; charset=utf-8'], [], [], '{"data": "value"}');
        $this->assertSame('value', $request->json('data'));
        $this->assertTrue($request->isJson());
    }

}
