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

namespace Core\Controllers;


use Core\Application\Application;
use Core\Config\Config;
use Core\Contracts\ResponseContract;
use Core\Contracts\RouterContract;
use Core\Contracts\ViewContract;
use Core\Foundation\DataCollection;
use Core\Request\Request;
use Core\Response\Response;
use Core\Router\Router;
use Core\View\View;

/**
 * Class BaseController
 * @package Core\Controllers
 */
class BaseController
{

    /**
     * Router object
     *
     * @var Router
     */
    protected $router;

    /**
     * @var Request
     */
    protected $request;
    
    /**
     * Application configuration
     *
     * @var Config
     */
    protected $conf;

    /**
     * Application base/core path
     *
     * @var null | string
     */
    protected $basePath;

    /**
     * Application folder path
     *
     * @var null | string
     */
    protected $appPath;

    /**
     * Sanitized $_POST parameter
     *
     * @var array
     */
    public $POST;

    /**
     * Sanitized $_GET parameters
     *
     * @var array
     */
    public $GET;

    /**
     * If application is currently using ssl
     *
     * @var bool
     */
    public $isSecure;

    /**
     * Response instance
     *
     * @var Response|object
     */
    public $response;

    /**
     * @param string $basePath
     * @param RouterContract|Router $router
     **/
    public function __construct($basePath, RouterContract $router)
    {
        $this->setPathBound($basePath);
        $this->router = $router;
        $this->request = $router->getRequest();
        $this->POST = $this->request->POST;
        $this->GET = $this->request->GET;
        $this->method = $this->request->getHttpMethod();

        $this->baseInit();
    }

    /**
     * @param $basePath
     */
    public function setPathBound($basePath)
    {
        $this->basePath = $basePath;
        $this->appPath = $basePath . DIRECTORY_SEPARATOR . 'app';
    }

    /**
     * Base init
     */
    private function baseInit()
    {

        // Add Page Title and Page Name (and other Variables defined in Router) to global
        $globalVariables = $this->router->getCurrentRoute()->getVariables();
        DataCollection::each($globalVariables, function($key, $value) {
            View::addVariable($key, $value);
        });
        
        
        $config = Config::getInstance();
        /** @var Request $request */
        $request = $this->router->getRequest();
        
        
        if ($config->get('metaAndTitleFromFile', false)) {
            $metaFilePath = $config->get('metaFile');
            $metaPath = $this->appPath . DS . ltrim($metaFilePath, "/");
            if (is_readable($metaPath)) {
                $metaContent = include($metaPath);
                $metas = isset($metaContent[$request->getPath()]) ? $metaContent[$request->getPath()] : '';
            } else {
                trigger_error(
                    htmlentities("{$config->get('$global.mataFile')} file not found or is not readable"),
                    E_USER_WARNING
                );
            }

            if (!empty($metas)) {
                if (isset($metas['pageTitle'])) {
                    View::addVariable('pageTitle', $metas['pageTitle']);
                    unset($metas['pageTitle']);
                }
                View::addVariable('metas', $metas);
            }

        }



        if (isset($this->conf['$global']['websiteUrl'])) {
            //$this->view->setTemplateVars('websiteUrl', $this->conf['$global']['websiteUrl']);
            View::addVariable('domainName', $this->conf['$global']['websiteUrl']);
        }

        if (isset($this->conf['$global']['domain'])) {
            //$this->view->setTemplateVars('domain', $this->conf['$global']['domain']);
            View::addVariable('domain', $this->conf['$global']['websiteUrl']);
        }

    }

    /**
     * Set empty response instance for easy access in Controllers
     *
     * @return Response|object
     * @throws \ErrorException
     */
    protected function setResponse()
    {
        $response = null;

        if (Application::serviceExists('Response')) {
            $response = Application::get('Response');
        }

        if (!$response instanceof ResponseContract) {
            $response = new Response();
        }

        $this->response = $response;
    }

    /**
     * @deprecated
     * Generates CSRF key
     */
    private function generateCSRFKey()
    {
        $key = sha1(microtime());
        $this->csrf = $_SESSION['csrf'] = empty($_SESSION['csrf']) ? $key : $_SESSION['csrf'];
        //$this->view->setTemplateVars('csrf', $this->csrf);
    }

    /**
     * @deprecated
     *
     * Default method for template rendering
     *
     * @return array
     */
    public function indexAction()
    {
        //$this->view->tplInfo['tpl'] = 'homepage/home.tpl';
    }


    /**
     * Makes string UTF-8 compliant
     *
     * @param $d
     * @return array|string
     */
    public function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = $this->utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }


    /**
     * @deprecated 
     * Resets Application Cache
     *
     * @throws \ErrorException
     */
    public function resetCache()
    {
        $routes = $this->conf['$routes'];
        /** @var \Core\Cache\AppCache $cache */
        $cache = Application::get('Cache');

        foreach($routes as $route => $params) {
            $key = md5($route . '_view_' . session_id());
            $cache->deleteCache($key);
        }

    }

}