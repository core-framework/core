<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 15/04/16
 * Time: 9:17 AM
 */

namespace Core\View;

use Core\Application\Application;
use Core\Config\Config;
use Core\Contracts\TemplateEngineContract;
use Core\Contracts\ViewContract;

class View implements ViewContract
{
    protected $application;

    protected $config;

    protected $showHeader;

    protected $showFooter;

    protected $layout = 'root.tpl';

    protected $template;

    protected $engine;

    protected static $templateDir;

    protected static $resourcesDir;

    /**
     * View constructor.
     * @param $application
     */
    public function __construct(Application $application = null)
    {
        if (!is_null($application)) {
            $application = Application::$app;
        }
        
        $this->application = $application;
        $this->init();
    }

    public function init()
    {
        $config = $this->getConfig();
        $engine = $this->getEngine();

        if ($config->get('view:leftDelimiter', false))
        {
            $engine->left_delimiter = $config->get('view:leftDelimiter', '<{');
            $engine->right_delimiter = $config->get('view:rightDelimiter', '}>');
        }

        $basePath = Application::getBasePath();
        $appPath = Application::getAppPath();
        $engine->setCompileDir($basePath . '/storage/smarty_cache/templates_c/');
        $engine->setConfigDir($basePath . '/storage/smarty_cache/config/');
        $engine->setCacheDir($basePath . '/storage/smarty_cache/cache/');
        $engine->setTemplateDir($appPath . '/Templates/');
        $engine->addTemplateDir(__DIR__ . '/Resources/BaseTemplates/');
        $engine->assign('basePath', $basePath);
        $engine->assign('appPath', $appPath);

        $engine->inheritance_merge_compiled_includes = false;
        $engine->caching = 1;
        $engine->cache_lifetime = $this->application->getTtl();
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
     * @return mixed
     */
    public function getConfig()
    {
        if (!$this->config instanceof Config) {
            $this->config = Application::get('Config');
        }
        return $this->config;
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
        if (!$this->engine instanceof TemplateEngineContract) {
            $engineName = getOne($this->getConfig()->get('templateEngine'), 'Smarty');
            $this->engine = Application::get($engineName);
        }
        return $this->engine;
    }

    /**
     * @param $variable
     * @param $value
     */
    public function set($variable, $value)
    {
        $this->getEngine()->assign($variable, $value);
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