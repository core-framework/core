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


use Core\Application\Console\Argument;
use Core\Application\Console\Option;
use Core\Contracts\Application\CliApplication;

interface Command
{

    /**
     * @return CliApplication
     */
    public function application();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @param $name
     * @param null $description
     * @param null $default
     * @param bool $required
     * @return mixed
     */
    public function addArgument($name, $description = null, $default = null, $required = false);

    /**
     * @param $name
     * @return bool
     */
    public function hasArgument($name);

    /**
     * @param null|string $name
     * @return Argument|Argument[]
     */
    public function getArgument($name = null);

    /**
     * @param $name
     * @param $shortName
     * @param $description
     * @param int $type
     * @return mixed
     */
    public function addOption($name, $shortName, $description, $type = Option::OPTION_OPTIONAL);

    /**
     * @param null $name
     * @return Option|Option[]
     */
    public function getOptions($name = null);

    /**
     * @param null|string $name
     * @return mixed
     */
    public function input($name = null);

    /**
     * Check if global options (--{key}) has been set
     *
     * @param null $name
     * @return mixed
     */
    public function options($name = null);

    /**
     * Initiate Command
     *
     * @return void
     */
    public function init();

    /**
     * Execute Command
     *
     * @param IOStream $io
     * @return mixed
     */
    public function execute(IOStream $io);

    /**
     * @param IOStream $io
     * @return void
     */
    public function showHelp(IOStream $io);
}