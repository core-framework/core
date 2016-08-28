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


namespace Core\Application\Console\Validators;

use Core\Contracts\Validators\OptionsValidator as OptionsValidatorInterface;

class OptionsValidator implements OptionsValidatorInterface
{

    protected $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options = [])
    {
        $this->options = $options;
    }

    public function addValidValue($value, $description = '')
    {
        $this->options[$value] = $description;
    }

    public function getValidValues()
    {
        return array_keys($this->options);
    }

    public function test($subject)
    {
        return in_array($subject, array_keys($this->options));
    }

    public function validate($subject, callable $successCallback = null, callable $failureCallback = null)
    {
        if (is_null($successCallback)) {
            $successCallback = [$this, 'success'];
        }
        if (is_null($failureCallback)) {
            $failureCallback = [$this, 'reject'];
        }
        $callback = $this->test($subject) ? $successCallback : $failureCallback;
        return call_user_func($callback, $subject);
    }

    public function success($subject = null)
    {
        return $subject;
    }

    public function reject($subject = null)
    {
        throw new \InvalidArgumentException("Invalid command line argument: {$subject}. For a detailed list of valid arguments for this command see help.");
    }
}