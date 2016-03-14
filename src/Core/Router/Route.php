<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 23/01/16
 * Time: 2:43 PM
 */

namespace Core\Router;


class Route
{
    protected $uri;

    protected $methods = [];

    protected $action;

    protected $controller;

    protected $parameters = [];

    protected $parameterNames = [];

    protected $options = [];

    /**
     * Route constructor.
     * @param $uri
     * @param array $methods
     * @param $action
     * @param array $options
     */
    public function __construct($uri, $methods, $action, $options = [])
    {
        $this->setUri($this->parseUri($uri));
        $this->setMethods($methods);
        $this->setAction($this->parseAction($action));

        if (!empty($options)) {
            $this->setOptions();
        }
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param $uri
     * @return Route
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param $methods
     * @return Route
     */
    public function setMethods($methods)
    {
        if (!is_string($methods)) {
            $methods = [$methods];
        }
        $this->methods = $methods;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param $action
     * @return Route
     */
    public function setAction($action)
    {
        if (is_string($action)) {
            $str = $action;
            $action = [];
            $action['controller'] = $str;
        }

        $this->action = $action;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param mixed $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getParameterNames()
    {
        return $this->parameterNames;
    }

    /**
     * @param mixed $parameterNames
     */
    public function setParameterNames(array $parameterNames)
    {
        $this->parameterNames = $parameterNames;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param $controller
     * @return Route
     */
    public function setController($controller)
    {
        if (!is_callable($controller) && !is_string($controller)) {
            throw new \InvalidArgumentException("Invalid controller of type {${gettype($controller)}} given. Expect string or callable");
        }

        $this->controller = $controller;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return Route
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }


    public function parseUri($uri)
    {
        if (preg_match_all('/\{([a-zA-Z0-9\:\?\=]+)\}/', $uri, $matches)) {
            foreach($matches[1] as $match) {
                $matchArr = explode(':', $match);
                $param = $matchArr[0];
                $this->parameterNames[] = $param;
                array_shift($matchArr);
                foreach($matchArr as $option) {
                    if ($option === '?') {
                        $this->parameters[$param] = ['isOptional' => true];
                    }
                    if ($option === 'num') {
                        $this->parameters[$param] = ['isNum' => true];
                    }
                    if (strpos($option, 'default') !== false) {
                        $this->parameters[$param] = ['default' => explode('=', $option)[1]];
                    }
                }
            }
        }
        return $uri;
    }

    public function parseAction($action)
    {
        if (is_callable($action)) {
            $this->setController($action);
        } elseif (isset($action['controller'])) {
            $this->setController($action['controller']);
        }

        if (is_array($action) && isset($action['prefix'])) {
            $this->prefix($action['prefix']);
        }

        return $action;
    }

    public function prefix($prefix)
    {
        $this->uri = rtrim($prefix, '/') . '/' . ltrim($this->uri, '/');
        return $this;
    }

}