<?php
/**
 * Pinoco: makes existing static web site dynamic transparently.
 * Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5
 *
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @package    Pinoco
 */

/**
 * Class Pinoco_Router
 */
class Pinoco_Router
{
    //public $routes;

    /** @var string */
    protected $path;

    /** @var bool */
    protected $handled;

    private $_tmp_params;

    /**
     * Constructor
     *
     * @param Pinoco $pinoco
     * @param string|null $path
     */
    public function __construct($pinoco, $path=null)
    {
        $this->pinoco = $pinoco;

        if (empty($path)) {
            // Pinoco::subpath is set when running hook script.
            // If not, it means taht current flow is out of hook, then
            // Pinoco::path should be default.
            $path = $this->pinoco->subpath;
            if (empty($path)) {
                $path = $this->pinoco->path;
            }
        }
        $this->path = $path;
        //$this->routes = array();

        $this->handled = false;
    }

    /**
     * Routing rules which binds URI path to any callable.
     *
     * '/index'  : Fixed route.
     * '/show/{id}'  : Parametrized one. Such path elements are passed to handler.
     * 'GET: /edit/{id}' or  'POST: /edit/{id}'  : Different HTTP methods can be different routes.
     * '*:*'  : Matches any patterns. Useful to be bound to Pinoco::notfound()  or forbidden().
     *
     * Specified handler is called immediately if the route matches. Please note that
     * this method is not a definition but invoker.
     *
     * @param string $route
     * @param callable $handler
     * @return $this
     */
    public function on($route, $handler)
    {
        if ($this->handled) {
            return $this;
        }

        $delimpos = strpos($route, ':');
        if ($delimpos !== false) {
            $method = strtoupper(trim(substr($route, 0, $delimpos)));
            $path = trim(substr($route, $delimpos + 1));
        }
        else {
            $method = '*';
            $path = $route;
        }

        if ($method != '*' && $method != $this->pinoco->request->method) {
            return $this;
        }

        $this->_tmp_params = array();
        $pathregexp = $path;
        $pathregexp = preg_replace('/\//', '\/', $pathregexp);
        $pathregexp = preg_replace('/\+/', '\+', $pathregexp);
        $pathregexp = preg_replace('/\*+/', '.+?', $pathregexp);
        $pathregexp = preg_replace_callback('/\{(.*?)\}/', array($this, '__each_path_args'), $pathregexp);

        if (preg_match('/^' . $pathregexp . '$/', $this->path, $matches)) {
            array_shift($matches);
            call_user_func_array($handler, $matches);
            $this->handled = true;
        }

        return $this;
    }

    public function __each_path_args($matches)
    {
        $name = $matches[1];
        $this->_tmp_params[] = $name;
        return '([^\/]+?)';
    }

    /**
     * Returns which a matching router was found and handled or not.
     *
     * @return bool
     */
    public function wasMatched()
    {
        return $this->handled;
    }
}