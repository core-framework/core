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

namespace Core\Tests\Reactor\Http\Psr7;


use Core\Reactor\Http\Psr7\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    const RFC3986_BASE = "http://a/b/c/d;p?q";

    public function testParsesProvidedUrl()
    {
        $uri = new Uri('https://michael:test@test.com:443/path/123?q=abc#test');

        // Standard port 443 for https gets ignored.
        $this->assertEquals(
            'https://michael:test@test.com/path/123?q=abc#test',
            (string) $uri
        );

        $this->assertEquals('test', $uri->getFragment());
        $this->assertEquals('test.com', $uri->getHost());
        $this->assertEquals('/path/123', $uri->getPath());
        $this->assertEquals(null, $uri->getPort());
        $this->assertEquals('q=abc', $uri->getQuery());
        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('michael:test', $uri->getUserInfo());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Malformed URL:
     */
    public function testValidatesUriCanBeParsed()
    {
        new Uri('///');
    }

    public function testCanTransformAndRetrievePartsIndividually()
    {
        $uri = (new Uri(''))
            ->withFragment('#test')
            ->withHost('example.com')
            ->withPath('path/123')
            ->withPort(8080)
            ->withQuery('?q=abc')
            ->withScheme('http')
            ->withUserInfo('user', 'pass');

        // Test getters.
        $this->assertEquals('user:pass@example.com:8080', $uri->getAuthority());
        $this->assertEquals('test', $uri->getFragment());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('path/123', $uri->getPath());
        $this->assertEquals(8080, $uri->getPort());
        $this->assertEquals('q=abc', $uri->getQuery());
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('user:pass', $uri->getUserInfo());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPortMustBeValid()
    {
        (new Uri(''))->withPort(100000);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPathMustBeValid()
    {
        (new Uri(''))->withPath([]);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testQueryMustBeValid()
    {
        (new Uri(''))->withQuery(new \stdClass);
    }
    
    public function testAllowsFalseUrlParts()
    {
        $url = new Uri('http://a:1/0?0#0');
        $this->assertSame('a', $url->getHost());
        $this->assertEquals(1, $url->getPort());
        $this->assertSame('/0', $url->getPath());
        $this->assertEquals('0', (string) $url->getQuery());
        $this->assertSame('0', $url->getFragment());
        $this->assertEquals('http://a:1/0?0#0', (string) $url);
        $url = new Uri('');
        $this->assertSame('', (string) $url);
        $url = new Uri('0');
        $this->assertSame('0', (string) $url);
        $url = new Uri('/');
        $this->assertSame('/', (string) $url);
    }

    public function testGetAuthorityReturnsCorrectPort()
    {
        // HTTPS non-standard port
        $uri = new Uri('https://foo.co:99');
        $this->assertEquals('foo.co:99', $uri->getAuthority());

        // HTTP non-standard port
        $uri = new Uri('http://foo.co:99');
        $this->assertEquals('foo.co:99', $uri->getAuthority());

        // No scheme
        $uri = new Uri('foo.co:99');
        $this->assertEquals('foo.co:99', $uri->getAuthority());

        // No host or port
        $uri = new Uri('http:');
        $this->assertEquals('', $uri->getAuthority());

        // No host or port
        $uri = new Uri('http://foo.co');
        $this->assertEquals('foo.co', $uri->getAuthority());
    }

    public function pathTestProvider()
    {
        return [
            // Percent encode spaces.
            ['http://foo.com/baz bar', 'http://foo.com/baz%20bar'],
            // Don't encoding something that's already encoded.
            ['http://foo.com/baz%20bar', 'http://foo.com/baz%20bar'],
            // Percent encode invalid percent encodings
            ['http://foo.com/baz%2-bar', 'http://foo.com/baz%252-bar'],
            // Don't encode path segments
            ['http://foo.com/baz/bar/bam?a', 'http://foo.com/baz/bar/bam?a'],
            ['http://foo.com/baz+bar', 'http://foo.com/baz+bar'],
            ['http://foo.com/baz:bar', 'http://foo.com/baz:bar'],
            ['http://foo.com/baz@bar', 'http://foo.com/baz@bar'],
            ['http://foo.com/baz(bar);bam/', 'http://foo.com/baz(bar);bam/'],
            ['http://foo.com/a-zA-Z0-9.-_~!$&\'()*+,;=:@', 'http://foo.com/a-zA-Z0-9.-_~!$&\'()*+,;=:@'],
        ];
    }

    /**
     * @dataProvider pathTestProvider
     */
    public function testUriEncodesPathProperly($input, $output)
    {
        $uri = new Uri($input);
        $this->assertEquals((string) $uri, $output);
    }

    public function testDoesNotAddPortWhenNoPort()
    {
        $uri = new Uri('//bar');
        $this->assertEquals('bar', (string) $uri);
        $this->assertEquals('bar', $uri->getHost());
    }

    public function testAllowsForRelativeUri()
    {
        $uri = (new Uri)->withPath('foo');
        $this->assertEquals('foo', $uri->getPath());
        $this->assertEquals('foo', (string) $uri);
    }

    public function testAddsSlashForRelativeUriStringWithHost()
    {
        $uri = (new Uri)->withPath('foo')->withHost('bar.com');
        $this->assertEquals('foo', $uri->getPath());
        $this->assertEquals('bar.com/foo', (string) $uri);
    }

    /**
     * @dataProvider pathTestNoAuthority
     */
    public function testNoAuthority($input)
    {
        $uri = new Uri($input);

        $this->assertEquals($input, (string) $uri);
    }

    public function pathTestNoAuthority()
    {
        return [
            // path-rootless
            ['urn:example:animal:ferret:nose'],
            // path-absolute
            ['urn:/example:animal:ferret:nose'],
            ['urn:/'],
            // path-empty
            ['urn:'],
            ['urn'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Malformed URL:
     */
    public function testNoAuthorityWithInvalidPath()
    {
        $input = 'urn://example:animal:ferret:nose';
        $uri = new Uri($input);
    }

    public function testExtendingClassesInstantiates()
    {
        // The non-standard port triggers a cascade of private methods which
        //  should not use late static binding to access private static members.
        // If they do, this will fatal.
        $this->assertInstanceOf(
            '\Core\Tests\Reactor\Http\Psr7\ExtendingClassTest',
            new ExtendingClassTest('http://h:9/')
        );
    }
}
class ExtendingClassTest extends Uri
{
}
