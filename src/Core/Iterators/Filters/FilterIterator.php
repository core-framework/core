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


use Iterator;

abstract class FilterIterator extends \FilterIterator
{
    /**
     * This is a workaround for the problem with \FilterIterator 
     * 
     * @param $iterator
     */
    public function __construct(Iterator $iterator)
    {
        parent::__construct($iterator);
        $this->rewind();
    }


    /**
     * This is a workaround for the problem with \FilterIterator leaving inner \FilesystemIterator in wrong state after
     * rewind in some cases.
     *
     * @see FilterIterator::rewind()
     */
    public function rewind()
    {
        if (PHP_VERSION_ID > 50607 || (PHP_VERSION_ID > 50523 && PHP_VERSION_ID < 50600)) {
            parent::rewind();

            return;
        }

        $iterator = $this;
        while ($iterator instanceof \OuterIterator) {
            $innerIterator = $iterator->getInnerIterator();

            if ($innerIterator instanceof \FilesystemIterator) {
                $innerIterator->next();
                $innerIterator->rewind();
            }
            $iterator = $iterator->getInnerIterator();
        }

        parent::rewind();
    }
}