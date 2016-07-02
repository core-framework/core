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


namespace Core\Iterators\Filters;

use RecursiveIterator;

abstract class RecursiveRegexFilter extends \RecursiveRegexIterator
{
    protected $regex;
    protected $iterator;

    /**
     * RegexFilter constructor.
     * @param \RecursiveIterator $iterator
     * @param string $regex
     */
    public function __construct(RecursiveIterator $iterator, $regex)
    {
        $this->iterator = $iterator;
        $this->regex = $this->toRegex($regex);
        parent::__construct($iterator, $this->regex);
    }

    /**
     * @param $pattern
     * @param array $options
     * @return string
     */
    public function toRegex($pattern, $options = [])
    {
        $pattern = str_replace(array('\*', '\?'), array('.*','.'), preg_quote($pattern));
        $pattern = '/' . $pattern . '/';
        if (!empty($options)) {
            $pattern .= implode('', $options);
        }

        return $pattern;
    }
}