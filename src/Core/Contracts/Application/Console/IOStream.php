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


namespace Core\Contracts\Application\Console;


interface IOStream
{
    /**
     * Method to get current CLI Argument(s)
     *
     * @return mixed
     */
    public function getArgv();

    /**
     * Returns stream
     *
     * @param $type
     * @return bool
     */
    public function getStream($type);


    /**
     * Takes msg as a parameter and validates and returns the input. Returns false if input is not valid
     *
     * @param $msg - The question or message
     * @param $callback - anonymous function to validate input. It should returns true if valid or false if not valid
     * @param $format - To prompt the user of the valid format.
     * @param null $default - default accepted value. if set and input is null then this value will be returned (qn or msg will not be repeated in this case)
     * @param int $repeat - The no. of times to ask the again, if input is invalid, before throwing an error
     * @return mixed - returns the input
     */
    public function askAndValidate($msg, $callback, $format, $default = null, $repeat = 2);


    /**
     * Outputs the given message and returns the value. If options ($opt) are set then its will return false if input value does not match one of the given options. Typically used for simple yes | no questions
     *
     * @param $message - The message to output or question to ask
     * @param null $default - The default value to return if input is null
     * @param null $options - The set of input options (input must match one of the options)
     * @return bool|string - returns the input value
     */
    public function ask($message, $default = null, $options = null);


    /**
     * Prints error message with specific formatting
     *
     * @param $msg
     * @param $exception
     * @throws \ErrorException
     */
    public function showErr($msg, $exception = null);


    /**
     * Output text
     *
     * @param $text
     * @param null $foreColor
     * @param null $backColor
     * @param string $format
     */
    public function write($text, $foreColor = null, $backColor = null, $format = "%s");


    /**
     * Outputs a single line
     *
     * @param $msg - message to output
     * @param null $foreColor - the text color
     * @param null $backColor - the background color
     * @param int $options - Display options like bold, underscore, blink, etc;
     */
    public function writeln($msg = "", $foreColor = null, $backColor = null, $options = null);


    /**
     * For multiple choice based questions or messages
     *
     * @param $introMsg - the question or message to output
     * @param array $list - the list of choices to display
     * @param null $repeat - the no. of times to repeat the question
     * @return bool|mixed|string
     * @throws \ErrorException
     */
    public function choice($introMsg, array $list, $repeat = null);


    /**
     * To output a multi-colored line. Each string to be colored must be a separate word (spaced string) and the color is determined buy the color specified by :color after string. Ex: 'some:green random:yellow string:red'
     *
     * @param $line - the message (with color specification) to output
     * @param null $format - the output format
     */
    public function writeColoredLn($line, $format = null);

    /**
     * Returns the user Input (from stream)
     *
     * @return string
     */
    public function getInputLine();

    /**
     * Returns a multi-line user input (from stream)
     *
     * @return string
     */
    public function getInputMultiLine();

}