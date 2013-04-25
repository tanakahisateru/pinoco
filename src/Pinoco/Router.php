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
    /** @var bool */
    protected $matched;

    private $_tmp_params;

    /**
     * Constructor
     *
     * @param Pinoco $pinoco
     */
    public function __construct($pinoco)
    {
        $this->pinoco = $pinoco;
        $this->matched = false;
    }

    /**
     * Routing rules which binds URI path to any callable.
     *
     * '/index'  : Fixed route.
     * 'index'  : Fixed route in relative path from current hook.
     * '/show/{id}'  : Parametrized one. Such path elements are passed to handler.
     * 'GET: /edit/{id}' or  'POST: /edit/{id}'  : Different HTTP methods can be different routes.
     * '*:*'  : Matches any patterns. Useful to be bound to Pinoco::notfound()  or forbidden().
     *
     * Specified handler is called immediately if the route matches. Please note that
     * this method is not a definition but invoker.
     *
     * @param string|array $route
     * @param callable $handler
     * @return $this
     */
    public function on($route, $handler)
    {
        if ($this->matched) {
            return $this;
        }

        if (is_array($route)) {
            foreach ($route as $r) {
                $this->on($r, $handler);
            }
            return $this;
        }

        list($method, $path) = $this->extractRoute($route);

        if ($this->matchesWithMethod($method)) {
            return $this;
        }

        $matchParams = $this->matchesWithPath($path);
        if (!is_null($matchParams)) {
            call_user_func_array($handler, $matchParams);
            $this->matched = true;
        }

        return $this;
    }

    /**
     * Specified route is ignored and delegated to the next script step.
     * This method is useful to ignore matching patterns below. (e.g. 'z*:*')
     *
     * @param string|array $route
     * @return $this
     */
    public function pass($route)
    {
        if ($this->matched) {
            return $this;
        }

        if (is_array($route)) {
            array_map(array($this, 'pass'), $route);
            return $this;
        }

        list($method, $path) = $this->extractRoute($route);

        if ($this->matchesWithMethod($method)) {
            return $this;
        }

        $matchParams = $this->matchesWithPath($path);
        if (!is_null($matchParams)) {
            $this->matched = true;
        }

        return $this;
    }

    /**
     * Returns which a matching router was found and handled or not.
     *
     * @return bool
     */
    public function wasMatched()
    {
        return $this->matched;
    }

    /**
     * @param string $route
     * @return array
     */
    private function extractRoute($route)
    {
        $delimpos = strpos($route, ':');
        if ($delimpos !== false) {
            $method = strtoupper(trim(substr($route, 0, $delimpos)));
            $path = trim(substr($route, $delimpos + 1));
            return array($method, $path);
        } else {
            $method = '*';
            $path = trim($route);
            return array($method, $path);
        }
    }

    /**
     * @param $path
     * @return array|null
     */
    private function matchesWithPath($path)
    {
        if (preg_match('|^/|', $path)) {
            $uri = $this->pinoco->path;
        } else {
            // Pinoco::subpath is set when running hook script.
            // If not, it means taht current flow is out of hook, then
            // Pinoco::path should be default instead.
            $uri = !is_null($this->pinoco->subpath) ? $this->pinoco->subpath : $this->pinoco->path;
        }

        $this->_tmp_params = array();
        $pathregexp = $path;
        $pathregexp = preg_replace('/\|/', '\|', $pathregexp);
        $pathregexp = preg_replace('/\+/', '\+', $pathregexp);
        $pathregexp = preg_replace('/\*+/', '.+?', $pathregexp);
        $pathregexp = preg_replace_callback('/\{(.*?)\}/', array($this, '__each_path_args'), $pathregexp);

        if (preg_match('|^' . $pathregexp . '$|', $uri, $matches)) {
            array_shift($matches);
            return $matches;
        }
        else {
            return null;
        }
    }

    public function __each_path_args($matches)
    {
        $name = $matches[1];
        $this->_tmp_params[] = $name;
        return '([^/]+?)';
    }

    /**
     * @param $method
     * @return bool
     */
    private function matchesWithMethod($method)
    {
        return $method != '*' && $method != $this->pinoco->request->method;
    }
}