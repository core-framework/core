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


namespace Core\Facades;

use Core\Reactor\Facade;
use Core\View\SmartyEngine;
use Core\Contracts\View as ViewInterface;

/**
 * Class View
 * @package Core\View
 * @method static bool isShowHeader()
 * @method static bool isShowFooter()
 * @method static void setLayout($layout)
 * @method static void setTemplate($template)
 * @method static mixed getEngine()
 * @method static bool has($template)
 * @method static void set($variable, $value)
 * @method static string fetch()
 * @method static string getTemplateDir()
 * @method static string setTemplateDir($templateDir)
 * @method static string getResourcesDir()
 * @method static void setResourcesDir($resourcesDir)
 * @method static ViewInterface make($template, array $parameters = [])
 */
class View extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getName()
    {
        return 'View';
    }
}