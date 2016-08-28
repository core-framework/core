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

namespace Core\View;

use Core\Application\Application;
use Core\Contracts\Config;
use Core\Contracts\TemplateEngineContract;
use Core\Contracts\View as ViewInterface;

class View implements ViewInterface
{
    protected $application;

    protected $config;

    protected $showHeader = true;

    protected $showFooter = true;

    protected $layout = 'root.tpl';

    protected $template;

    protected $engine;

    protected static $templateDir;

    protected static $resourcesDir;

    /**
     * View constructor.
     * @param $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->init();
    }

    public function init()
    {
        $config = $this->getConfig();
        $engine = $this->getEngine();

        if ($config->get('template.leftDelimiter', false))
        {
            $engine->left_delimiter = $config->get('template.leftDelimiter', '<{');
            $engine->right_delimiter = $config->get('template.rightDelimiter', '}>');
        }

        $basePath = $this->application->basePath();
        $appPath = $this->application->appPath();
        $engine->setCompileDir($basePath . '/storage/smarty_cache/templates_c/');
        $engine->setConfigDir($basePath . '/storage/smarty_cache/config/');
        $engine->setCacheDir($basePath . '/storage/smarty_cache/cache/');
        $engine->setTemplateDir(
            $this->application->getRealPath(
                $config->get('template.dir', $basePath . '/web/Templates/')
            )
        );
        $engine->addTemplateDir(__DIR__ . '/Resources/BaseTemplates/');
        $this->addTemplateDirs($config->get('template.dirs', []));
        
        $engine->assign('basePath', $basePath);
        $engine->assign('appPath', $appPath);
        $engine->assign('layout', $this->layout);
        $engine->assign('showHeader', $this->showHeader);
        $engine->assign('showFooter', $this->showFooter);

        $engine->inheritance_merge_compiled_includes = $config->get('template.mergeCompiled', false);
        $engine->caching = $config->get('template.caching', 1);
        $engine->compile_check = $config->get('template.compileCheck', true);
        $engine->cache_lifetime = $config->get('app.ttl', 60);
    }

    /**
     * Bulk add Template directories
     *
     * @param array $dirs
     */
    public function addTemplateDirs(array $dirs)
    {
        foreach($dirs as $i => $dir) {
            $this->getEngine()->addTemplateDir($this->application->getRealPath($dir));
        }
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->application->getConfig();
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function isShowHeader()
    {
        return $this->showHeader;
    }

    /**
     * @param bool $showHeader
     */
    public function setShowHeader($showHeader)
    {
        $this->showHeader = boolval($showHeader);
    }

    /**
     * @return bool
     */
    public function isShowFooter()
    {
        return $this->showFooter;
    }

    /**
     * @param bool $showFooter
     */
    public function setShowFooter($showFooter)
    {
        $this->showFooter = boolval($showFooter);
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return SmartyEngine
     */
    public function getEngine()
    {
        if (!$this->engine instanceof TemplateEngineContract || !$this->engine instanceof \Smarty) {
            $engineName = $this->getConfig()->get('templateEngine', 'Smarty');
            $this->engine = $this->application->get($engineName);
        }
        return $this->engine;
    }

    /**
     * @param $variable
     * @param $value
     */
    public function set($variable, $value)
    {
        if (strContains('.', $variable)) {
            $this->dotAssign($variable, $value);
        } else {
            $this->getEngine()->assign($variable, $value);
        }
    }

    protected function dotAssign($var, $val)
    {
        $vars = explode('.',$var);
        $currentVal = $this->getEngine()->getTemplateVars($vars[0]);
        if (is_array($currentVal)) {
            $currentVal[$vars[1]] = $val;
        } else {
            $currentVal = [$vars[1] => $val];
        }

        $this->getEngine()->assign($vars[0], $currentVal);
    }

    public function clearCache($tpl = null)
    {
        if (is_null($tpl)) {
            $this->getEngine()->clearAllCache();
        } else {
            $this->getEngine()->clearCache($tpl);
        }
    }

    public function clearCompiled($tpl = null, $compileId = null, $expires = null)
    {
        $this->getEngine()->clearCompiledTemplate($tpl, $compileId, $expires);
    }

    /**
     * @inheritdoc
     */
    public function fetch()
    {
        return $this->getEngine()->fetch($this->template);
    }

    /**
     * @param mixed $engine
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * @return mixed
     */
    public static function getTemplateDir()
    {
        return self::$templateDir;
    }

    /**
     * @param mixed $templateDir
     */
    public static function setTemplateDir($templateDir)
    {
        self::$templateDir = $templateDir;
    }

    /**
     * @return mixed
     */
    public static function getResourcesDir()
    {
        return self::$resourcesDir;
    }

    /**
     * @param mixed $resourcesDir
     */
    public static function setResourcesDir($resourcesDir)
    {
        self::$resourcesDir = $resourcesDir;
    }
    
    /**
     * Makes a View
     *
     * @param $template
     * @param array $parameters
     * @return View
     */
    public static function make($template, array $parameters = [])
    {
        $instance = Application::get('View');
        $instance->setTemplate($template);
        $engine = $instance->getEngine();
        if (!empty($parameters))
        {
            foreach($parameters as $key => $val) {
                $engine->assign($key, $val);
            }
        }
        
        return $instance;
    }

}