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


namespace Core\Contracts\Application;


use Core\Application\Console\IOStream;
use Core\Contracts\Application;
use Core\Contracts\Cache;
use Core\Contracts\Config;
use Core\Contracts\Events\Dispatcher;
use Core\Contracts\Reactor\Runnable;

interface CliApplication extends Application
{
    /**
     * @param $name
     * @param $shortName
     * @param $description
     * @param $isRequired
     * @return mixed
     */
    public function addGlobalOptions($name, $shortName, $description, $isRequired = false);

    /**
     * @return array
     */
    public function getGlobalOptions();

    /**
     * @param $name
     * @param $class
     * @return void
     */
    public function addCommand($name, $class);

    /**
     * @param $name
     * @return bool
     */
    public function hasCommand($name);

    /**
     * @param $name
     * @return mixed
     */
    public function getCommand($name);

    /**
     * @param null|string $argumentName
     * @return mixed|array
     */
    public function input($argumentName = null);

    /**
     * @param null|string $optionName
     * @return mixed|array
     */
    public function inputOptions($optionName = null);

    /**
     * @return void
     */
    public function showHelp();
}