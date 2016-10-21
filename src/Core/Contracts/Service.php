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

namespace Core\Contracts;

/**
 * Interface serviceInterface
 * @package Core\Container
 */
interface Service
{
    /**
     * Creates a service instance with the given params
     *
     * @param string $name
     * @param \Closure|string $definition
     * @param bool $shared
     * @throws \ErrorException
     */
    public function __construct($name, $definition, $shared = false);

    /**
     * Returns the definition of the service
     *
     * @return callable|string
     */
    public function getDefinition();

    /**
     * Sets the definition of the service
     *
     * @param mixed $definition
     */
    public function setDefinition($definition);

    /**
     * Returns whether service instance is shared
     *
     * @return bool
     */
    public function getShared();

    /**
     * Sets whether service instances are shared
     *
     * @param bool $bool
     * @throws \ErrorException
     */
    public function setShared($bool);

    /**
     * Returns the previously set service arguments
     *
     * @return array
     */
    public function getArguments();

    /**
     * Sets arguments to be passed to service constructor
     *
     * @param array $args
     */
    public function setArguments(array $args);

    /**
     * Returns the method set for the service
     *
     * @return string
     */
    public function getMethod();

    /**
     * Sets the method for class instantiation
     *
     * @param string $method
     * @throws \ErrorException
     */
    public function setMethod($method);

    /**
     * Returns the set method arguments
     *
     * @return mixed
     */
    public function getMethodArgs();

    /**
     * Sets the method arguments
     *
     * @param mixed $methodArgs
     */
    public function setMethodArgs($methodArgs);
}
