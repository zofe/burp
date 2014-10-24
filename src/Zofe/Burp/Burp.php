<?php

namespace Zofe\Burp;

/**
 * Class Burp
 * simple php router, to check uri/query string and do something.
 * It also has a "named route" helper to build your application urls
 *
 * @package Zofe\Burp
 *
 * @method public static get($uri=null, $query=null, Array $route)
 * @method public static post($uri=null, $query=null, Array $route)
 * @method public static patch($uri=null, $query=null, Array $route)
 * @method public static put($uri=null, $query=null, Array $route)
 * @method public static delete($uri=null, $query=null, Array $route)
 */
class Burp
{
    public static $routes = array();
    public static $qs = array();
    public static $remove = array();
    public static $methods = array();
    public static $callbacks = array();

    public static $tocatch = array();
    public static $catched = array();
    public static $missing_callback;

    public static function missing(\Closure $missing)
    {
        self::$missing_callback = $missing;
    }

    /**
     * little bit magic here, to catch all http methods to define a named route
     * <code>
     *    Router::get(null, 'page=(\d+)', array('as'=>'page', function ($page) {
     *        //with this query string: ?page=n  this closure will be triggered
     *    }));
     * </code>
     * @param $method
     * @param $params
     * @return static
     */
    public static function __callstatic($method, $params)
    {
        $params = self::fixParams($method, $params);

        $uri = ltrim($params[0],"/");
        $qs = $params[1];

        $name = $params[2]['as'];
        self::$routes[$name] = $uri;
        self::$qs[$name] = $qs;
        self::$remove[$name] = array();
        self::$methods[$name] = strtoupper($method);
        self::$callbacks[$name] = $params[2][0];

        //this is a strict rule
        if ($uri!= '' and $uri[0]=== '^') {
            self::$tocatch[] = $name;
        }

        return new static();
    }

    /**
     * dispatch the router: check for all defined routes and call closures
     */
    public static function dispatch()
    {
        $uri = strtok($_SERVER["REQUEST_URI"],'?');
        $qs = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
        $method = $_SERVER['REQUEST_METHOD'];
        
        
        foreach (self::$routes as $name=>$route) {


            $replaces = self::parsePattern($route);
            foreach ($replaces as $pattern) {
                $conditional = (substr($pattern, -1, 1) === '?') ? true : false;
                $pattern = rtrim($pattern, '?');

                if (array_key_exists($pattern, self::$routes)) {

                    if ($conditional) {
                        //dd($route, $pattern);
                        $route = preg_replace('#\{'.$pattern.'\?\}#', '('.self::$routes[$pattern].')?', $route);
                    } else {
                        $route = preg_replace('#\{'.$pattern.'\}#', self::$routes[$pattern], $route);
                    }
                }

            }

            $matched = array();
            if ($route=='' || preg_match('#' . $route . '#', $uri, $matched) && (self::$methods[$name] == 'ANY' || self::$methods[$name] == $method)) {
                
                array_shift($matched);

                if (self::$qs[$name]!='' && preg_match('#' . self::$qs[$name] . '#', $qs, $qsmatched)) {

                    array_shift($qsmatched);

                    $matched = array_merge($matched, $qsmatched);
                    self::$catched[] = $name;
                    //call_user_func_array(self::$callbacks[$name], $matched);
                    BurpEvent::listen($name, self::$callbacks[$name]);
                    BurpEvent::queue($name, $matched);

                } elseif (self::$qs[$name] == '') {
                    self::$catched[] = $name;
                    //call_user_func_array(self::$callbacks[$name], $matched);
                    BurpEvent::listen($name, self::$callbacks[$name]);
                    BurpEvent::queue($name, $matched);
                }
            }

        }

        //call missing if needed
        if (!is_null(self::$missing_callback) ) {
            $diff = array_diff(self::$tocatch, self::$catched);
            if (count($diff) >= count(self::$tocatch)) {
                call_user_func(self::$missing_callback);
                BurpEvent::listen('missing', self::$callbacks[$name]);
                BurpEvent::fire('missing');

            }

        }

        BurpEvent::flushAll();
    }

    public static function isRoute($name, $params = array())
    {
        $uri = strtok($_SERVER["REQUEST_URI"],'?');
        $qs = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
        $method = $_SERVER['REQUEST_METHOD'];
        $route = @self::$routes[$name];

        $matched = array();
        if ($route=='' || preg_match('#' . $route . '#', $uri, $matched) && (self::$methods[$name] == 'ANY' || self::$methods[$name] == $method)) {

            array_shift($matched);

            if (self::$qs[$name]!='' && preg_match('#' . self::$qs[$name] . '#', $qs, $qsmatched)) {

                array_shift($qsmatched);

                $matched = array_merge($matched, $qsmatched);
                
                if (count($params)) {
                   return  ($matched == $params) ? true : false;
                } else {
                    return true;
                }
                
            } elseif (self::$qs[$name] == '') {
                
                if (count($params)) {
                    return  ($matched == $params) ? true : false;
                } else {
                    return true;
                }

            }
        }

        return false;
    }

    /**
     * check if route method call is correct, and try to fix if not
     * @param $method
     * @param $params
     */
    private static function fixParams($method, $params)
    {
        if (! in_array(strtolower($method), array('get','post','patch','put','delete','any','head')))
            throw new \BadMethodCallException("valid methods are 'get','post','patch','put','delete','any','head'");

        //force route_name to be the uri
        if  (is_array($params) && isset($params[2]) && ($params[2] instanceof \Closure)) {
            $params[2] = array('as'=>$params[0], $params[2]);
        }
        
        if (count($params)<3 ||
            !is_array($params[2]) ||
            !array_key_exists('as', $params[2]) ||
            !array_key_exists('0', $params[2]) ||
            !($params[2][0] instanceof \Closure) )
            throw new \InvalidArgumentException('third parameter should be an array containing a
                                                   valid callback: array(\'as\'=>\'routename\', function () { })  ');

        return $params;
    }

    /**
     * queque to remove from url one or more named route
     *
     * @return static
     */
    public function remove()
    {
        $routes = (is_array(func_get_arg(0))) ? func_get_arg(0) : func_get_args();

        end(self::$routes);
        self::$remove[key(self::$routes)] = $routes;

        return new static();
    }

    /**
     * return a link starting from routename and params
     * like laravel link_to_route() but working with rapyd widgets/params
     *
     * @param $name route name
     * @param $param  one or more params required by the route
     * @return string
     */
    public static function linkRoute($name, $params = array(), $url = null)
    {
        //starting defining url and qs
        $url = ($url != '') ? $url : $_SERVER["REQUEST_URI"];
        $url_arr = explode('?', $url);
        $url = $url_arr[0];
        $qs = (isset($url_arr[1])) ? $url_arr[1] : '';
        if (!is_array($params)) $params = (array) $params;

        
        //if a stric-uri?
        if (isset(self::$routes[$name][0]) and self::$routes[$name][0]=== '^') {

            $route = self::$routes[$name];
            //in this case remove conditional patterns
            $replaces = self::parsePattern($route);
            foreach ($replaces as $pattern) {
                $conditional = (substr($pattern, -1, 1) === '?') ? true : false;
                $pattern = rtrim($pattern, '?');

                if (array_key_exists($pattern, self::$routes)) {

                    if ($conditional) {
                        $route = preg_replace('#\{'.$pattern.'\?\}#', '', $route);
                    } else {
                        $route = preg_replace('#\{'.$pattern.'\}#', self::$routes[$pattern], $route);
                    }
                }

            }
            $route = ltrim($route, '^');
            $route = rtrim($route, '$');
            if (preg_match_all('#\(.*\)#U',$route, $matches)) {
                foreach ($params as $key=>$param) {
                    $route = str_replace($matches[0][$key],$param, $route);
                }
            }
            return  $route;
        }



        //If required we remove other routes (from segments or qs)
        if (count(self::$remove[$name])) {
            foreach (self::$remove[$name] as $route) {
                if (self::$routes[$route]) {
                    $url = preg_replace('#(\/?)'.self::$routes[$route].'#', '', $url);
                }
                if (self::$qs[$route]) {
                    $url = preg_replace('#(&?)'.self::$qs[$route].'#', '', $url);
                }

            }
        }

        //if this route works with uri
        //I check for all params to build the append segment with given params,
        //then I strip current rule and append new one.
        if (self::$routes[$name]) {
            $append =  self::$routes[$name];
            if (preg_match_all('#\(.*\)#U',$append, $matches)) {
                foreach ($params as $key=>$param) {
                    $append = str_replace($matches[0][$key],$param, $append);
                }
            }
            $url = preg_replace('#(\/?)'.self::$routes[$name].'#', '', $url);
            $url = $url."/".$append;
            $url = str_replace('//','/',$url);

        }

        //if this route works on query string
        //I check for all params to buid the "append" string with given params,
        //then I strip current rule and append the new one.
        if (self::$qs[$name]) {
            $append =  self::$qs[$name];
            if (preg_match_all('#\(.*\)#U',$append, $matches)) {
                foreach ($params as $key=>$param) {
                    $append = str_replace($matches[0][$key],$param, $append);
                }
            }
            $qs = preg_replace('#(&?)'.self::$qs[$name].'#', '', $qs);
            $qs = str_replace('&&','&',$qs);
            $qs = rtrim($qs, '&');
            $qs =  $qs .'&'.$append;
            $qs = ltrim($qs, '&');

        }

        if ($qs != '') $qs = '?'.$qs;
        if ($url == '') $url = '/';
        return $url.$qs;
    }

    private static function parsePattern($pattern)
    {
        if (preg_match_all('/\{(\w+\??)\}/is', $pattern, $matches)) {
            return $matches[1];
        }

        return array();
    }
}
