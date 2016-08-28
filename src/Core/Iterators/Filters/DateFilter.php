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

class DateFilter extends \RecursiveFilterIterator
{

    protected $targetDate;

    protected $operand;

    /**
     * @inheritDoc
     */
    public function __construct(RecursiveIterator $iterator, $targetDate, $operand = '<')
    {
        $this->setDate($targetDate);
        $this->operand = $operand;
        parent::__construct($iterator);
    }

    public function setDate($date)
    {
        try {
            if ($this->isValidTimeStamp($date)) {
                $date = '@'.$date;
            }
            $date = new \DateTime($date);
            $this->targetDate = (int)$date->format('U');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid date given: {$date}");
        }
    }

    /**
     * Checks if string is a valid Unix timestamp
     *
     * @link http://stackoverflow.com/questions/2524680/check-whether-the-string-is-a-unix-timestamp
     * @author http://stackoverflow.com/users/208809/gordon
     * @param $timestamp
     * @return bool
     */
    protected function isValidTimeStamp($timestamp)
    {
        return ((string)(int)$timestamp === $timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
    }

    /**
     * @inheritdoc
     */
    public function accept()
    {
        return $this->evaluate($this->current()->getMTime());
    }

    protected function evaluate($fileDate)
    {
        switch ($this->operand) {
            case '>':
                return $fileDate > $this->targetDate;

            case '<':
                return $fileDate < $this->targetDate;

            case '>=':
                return $fileDate >= $this->targetDate;

            case '<=':
                return $fileDate <= $this->targetDate;

            case '!=':
                return $fileDate != $this->targetDate;

            default:
                return $fileDate == $this->targetDate;
        }
    }

}