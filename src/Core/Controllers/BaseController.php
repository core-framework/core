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


use Core\Contracts\Application;
use Core\Config\Config;
use Core\Contracts\Response\Response;
use Core\Contracts\Router\Router;
use Core\Contracts\View;
use Core\Reactor\DataCollection;
use Core\Request\Request;

/**
 * Class BaseController
 * @package Core\Controllers
 */
class BaseController
{

    /**
     * @var Application $application
     */
    protected $application;

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
     * BaseController constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->setPathBound($application->basePath());
        $this->router = $application->getRouter();
        $this->request = $application->getRequest();
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
        $globalVariables = $this->router->getCurrentRoute()->getData();
        $view = $this->application->getView();
        DataCollection::each($globalVariables, function($key, $value) use ($view) {
            $view->set($key, $value);
        });
        
        
        $config = $this->application->getConfig();
        $cache = $this->application->getCache();
        $request = $this->request;

        if ($config->get('app.metaAndTitleFromFile', false)) {
            $metaFilePath = $config->get('app.metaFile');
            $metaPath = $this->appPath . DS . ltrim($metaFilePath, "/");
            if (is_readable($metaPath)) {
                $metaContent = include($metaPath);
                $metas = isset($metaContent[$request->getPath()]) ? $metaContent[$request->getPath()] : '';
            } else {
                trigger_error(
                    htmlentities("{$config->get('app.mataFile')} file not found or is not readable"),
                    E_USER_WARNING
                );
            }

            if (!empty($metas)) {
                if (isset($metas['pageTitle'])) {
                    $view->set('pageTitle', $metas['pageTitle']);
                    unset($metas['pageTitle']);
                }
                $view->set('metas', $metas);
            }

        }


        $view->set('domainName', $config->get('app.websiteUrl', ''));
        $view->set('domain', $config->get('app.domain', ''));

        if ($cache->exists('csrf-token')) {
            $view->set('csrfToken', $cache->get('csrf-token'));
        } elseif (isset($_SESSION['csrf-token'])) {
            $view->set('csrfToken', $_SESSION['csrf-token']);
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
        $this->response = $this->application->getResponse();
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


}