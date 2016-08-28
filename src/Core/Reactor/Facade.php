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

namespace Core\Reactor;

use Core\Application\Application;
use Core\Container\Container;
use Core\Contracts\Reactor\Facade as FacadeInterface;

abstract class Facade implements FacadeInterface
{
    /**
     * is triggered when invoking inaccessible methods in a static context.
     *
     * @param $name string
     * @param $arguments array
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
     */
    static function __callStatic($name, $arguments)
    {
        $instance = static::kernel();
        
        if (!method_exists($instance, $name)) {
            throw new \BadMethodCallException(get_called_class() . " does not implement " . $name . " method.");
        }
        
        return call_user_func_array([$instance, $name], $arguments);
    }

    /**
     * @return string
     * @throws \BadMethodCallException
     */
    protected static function getName()
    {
        throw new \BadMethodCallException("Facade does not implement method getName.");
    }

    /**
     * @return object
     * @throws \ErrorException
     */
    public static function kernel()
    {
        return Application::get(static::getName());
    }
}