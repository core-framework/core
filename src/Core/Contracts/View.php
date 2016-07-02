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

use Core\Application\Application;
use Core\Config\Config;

/**
 * Interface ViewContract
 * @package Core\ViewContract
 */
interface View {

    /**
     * @return Application
     */
    public function getApplication();

    /**
     * @return Config
     */
    public function getConfig();

    /**
     * @return bool
     */
    public function isShowHeader();

    /**
     * @return bool
     */
    public function isShowFooter();

    /**
     * @return string
     */
    public function getLayout();

    /**
     * @param string $layout
     * @return void
     */
    public function setLayout($layout);

    /**
     * @return string
     */
    public function getTemplate();

    /**
     * @param string $template
     * @return void
     */
    public function setTemplate($template);

    /**
     * @return TemplateEngineContract
     */
    public function getEngine();

    /**
     * @param $variable
     * @param $value
     * @return void
     */
    public function set($variable, $value);
    
    /**
     * @return string
     * @throws \Exception
     * @throws \SmartyException
     */
    public function fetch();

    /**
     * @param string $templateDir
     * @return void
     */
    public static function setTemplateDir($templateDir);

    /**
     * @return string
     */
    public static function getTemplateDir();

    /**
     * @return string
     */
    public static function getResourcesDir();

    /**
     * @param string $resourcesDir
     * @return void
     */
    public static function setResourcesDir($resourcesDir);
}