<?php

namespace Alkane\Router;

use \Alkane\AlkaneAPI\AlkaneAPI;

/**
 * Router Class
 *
 * @category  Router
 * @package   Router
 * @author    Sadiq <sadiq.com.bd@gmail.com>
 * @copyright Copyright (c) 2022
 * @version   1.1.2
 * @package   Alkane\Router
 */

class Router extends AlkaneAPI {
    /**
     * @var array
     */
    private static $routes = array();

    /**
     * @var array
     */
    private static $pattern_routes = array();

    /**
     * @var array
     */
    private static $redirects = array();

    /**
     * @var array
     */
    private static $route_dirs = array();

    /**
     * @var array
     */
    private static $mime_types = array();

    /**
     * @var array
     */
    private static $route_params = array();

    /**
     * @var callback
     */
    private static $onError = null;

    /**
     * add route
     * @var $name
     * @var $path
     */
    public static function get(string $name, string $path) {
        if (file_exists($path)) {
            self::$routes[$name] = $path;
        }
        if (strpos($name, '{') !== false) {
            $path_split = str_split($name);
            $param = array();
            $i = 0;
            $addVal = false;
            foreach ($path_split as $k => $v) {
                if ($v == '{' || $addVal) {
                    if ($v == '}') {
                        $addVal = false;
                        $i++;
                        continue;
                    }
                    $addVal = true;
                    if ($v == '{') {
                        continue;
                    }
                    if (!empty($param[$i])) {
                        $param[$i] .= $v;
                    } else {
                        $param[$i] = $v;
                    }
                }
            }    
            $route_path = $name;
            $route_path = str_replace('/', '\/', $route_path);
            $pattern = '(.*)';
            foreach ($param as $key => $value) {
                $route_path = preg_replace('/\{'.$value.'\}/', $pattern, $route_path);
            }
            $route_path = '/^'.$route_path.'$/';
            self::$route_params[$route_path] = $param;
            self::$pattern_routes[$route_path] = $path;
        }
    }

    /**
     * @var $name
     * @var $path
     */
    public static function add_dirs(string $prefix, string $dir) {
        self::$route_dirs[$prefix] = $dir;
    }

    /**
     * @var $name
     * @var $path
     */
    public static function add_redirect(string $path, string $redirect) {
        self::$redirects[$path] = $redirect;
    }

    /**
     * @var $path
     */
    public static function is_redirect(string $path) {
        return isset(self::$redirects[$path]);
    } 

    /**
     * @var $path
     * is route exists
     */
    public static function is_route(string $path = null) {
        if ($path === null) {
            $path = self::current_path();
        }
        return isset(self::$routes[$path]);
    }

    /**
     * @var $path
     * is pattern route exists
     */
    public static function is_pattern_route(string $path = null) {
        if ($path === null) {
            $path = self::current_path();
        }
        foreach (self::$pattern_routes as $pattern => $route) {
            if (preg_match($pattern, $path, $matches)) {
                unset($matches[array_search($path, $matches)]);
                return array_values($matches);
            }
        }
        return false;
    }

    public static function get_pattern_route(string $path = null) {
        foreach (self::$pattern_routes as $pattern => $route) {
            if (preg_match($pattern, $path)) {
                return $route;
            }
        }
        return false;
    }

    /**
     * @var $path
     * @var $onError
     */
    public static function run(string $name = null, $onError = null) {

        //remove server info headers
        self::removeServerInfoHeaders();

        // sets the default route if route is null
        if ($name == null) {
            $name = self::current_path();
        }

        // if path is a redirect
        if (!empty($name)) {
            if (self::is_redirect($name)) {
                self::redirect_301(self::$redirects[$name]);
            }
        }

        // get mime types
        if (empty(self::$mime_types)) {
            self::mime_types_from_file('mime.types');
        }

        // @var $route_params
        $route_params = self::$route_params;
        self::$route_params = [];

        // if route exists then include it
        if (self::is_route($name)) {
            if (self::getFile(self::$routes[$name])) {
                return true;
            }
        }

        // if pattern route exists then include it & set GET custom params
        if ($matches = self::is_pattern_route($name)) {
            foreach ($route_params as $key => $value) {
                foreach ($value as $k => $v) {
                    if (preg_match($key, $name)) {
                        self::$route_params[$v] = $matches[$k];
                    }
                }
                
            }
            if (self::getFile(self::get_pattern_route($name))) {
                return true;
            }
        }
        
        // if path is a directory route
        $dir_prefix = self::get_dir_prefix($name);
        if (!empty(self::$route_dirs[$dir_prefix])) {
            $dir = self::$route_dirs[$dir_prefix];
            $name = ltrim(ltrim($name, '/'), $dir_prefix);
            $name = $dir . '/' . $name;
            if (self::getFile($name)) {
                return true;
            }
        }

        // handle error page
        if ($onError == null) {
            $onError = self::$onError;
        }
        self::errorHnadler($onError);
        return false;
    }

    /**
     * auto route
     */
    public static function getFile($file) {
        if (is_file($file) && !is_dir($file)) {            
            $file_ext = pathinfo($file, PATHINFO_EXTENSION);

            // set file content type by extension
            self::set_content_type(self::ext2mime($file_ext));

            if (strtolower($file_ext) == 'php') {
                // for dynamic php files
                self::includeFile($file);
            } else {
                // for static content
                readfile($file);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public static function current_path() {
        $path = parse_url(trim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH);
        return ($path == null || empty($path)) ? '' : urldecode($path);
    }

    /**
     * @return string
     */
    public static function current() {
        return self::current_path();
    }

    /**
     * @var $prefix
     * @var $dir
     */
    public static function route_dir($prefix, $dir){
        self::$route_dirs[$prefix] = $dir;
    }

    /**
     * @var $file
     */
    public static function route_from_json($file) {
        if (file_exists($file)) {
            $routes = file_get_contents($file);
            $routes = json_decode($routes);

            if (!empty($routes)) {
                foreach($routes as $r => $rr) {
                    switch ($r) {
                        case 'file':
                            foreach ($rr as $k => $v) {
                                if (preg_match('/(^\#)/', $k)) {
                                    continue;
                                }
                                self::get(trim($k, '/'), trim($v, '/'));
                            }
                            break;
                        case 'folder':
                        case 'dir':
                            foreach ($rr as $k => $v) {
                                if (preg_match('/(^\#)/', $k)) {
                                    continue;
                                }
                                self::route_dir(trim($k, '/'), trim($v, '/'));
                            }
                            break;
                        case 'redirect':
                            foreach ($rr as $k => $v) {
                                if (preg_match('/(^\#)/', $k)) {
                                    continue;
                                }
                                self::add_redirect(trim($k, '/'), trim($v, '/'));
                            }
                            break;
                    }
                }
            } else {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @var $file
     */
    public static function mime_types_from_file(string $file = 'mime.types') {
        $mime_types = array();
        if ($file = fopen($file, "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                if (!preg_match('/(^\#)/', $line)) {
                    $line = preg_split('/\s+/', $line);
                    for ($i = 1; $i < count($line); $i++) {
                        if (empty($line[$i])) {
                            continue;
                        }
                        $mime_types[strtolower($line[$i])] = strtolower($line[0]); 
                    }
                }
            }
            fclose($file);
        } 
        self::$mime_types = $mime_types;
    }

    /**
     * gets directory prefix from path
     * @var $path
     */
    public static function get_dir_prefix($path) {
        $path = trim($path, '/');
        $path = explode('/', $path);
        $prefix = '';
        if (!empty($path)) {
            $prefix = $path[0];
        }
        return $prefix;
    }

    /**
     * On error callback
     */
    public static function onError($callback) {
        self::$onError = $callback;
    }

    /**
     * handles onError
     * @var $onError
     */
    public static function errorHnadler($onError) {
        $errCode = http_response_code();
        if ($errCode == 200) {
            $errCode = 404;
            http_response_code($errCode);
        }
        self::set_content_type('text/html');

        if (is_callable($onError)) {
            $onError($errCode, self::httpStatusCode2Message($errCode));
        } elseif (is_callable(self::$onError)) {
            call_user_func_array(self::$onError, [
                $errCode, 
                self::httpStatusCode2Message($errCode)
            ]); 
        }
    }

    /**
     * get mime type from extension
     * @var $ext
     * @return string
     */
    public static function ext2mime($ext) { 
        if (isset(self::$mime_types[$ext])) {
            return self::$mime_types[$ext];
        } else {
            return 'text/html';
        }
    }

    /**
     * gets request parametrs
     */
    public static function get_request_params() {
        return self::$route_params;
    }

    /**
     * redirects to $url
     * @var $url
     */
    public static function redirect_301(string $url) {
        header("HTTP/1.1 301 Moved Permanently"); 
        header('Location: ' . $url);
        exit;
    }

    /**
     * set content type
     */
    public static function set_content_type(string $type) {
        header('Content-Type: ' . $type . '; charset=UTF-8');
    }

    /**
     * Removes Server information from header
     * @return void
     */
    public static function removeServerInfoHeaders() {
        $headers = [
            'X-Powered-By'
        ];  
        foreach ($headers as $header) {
            @header_remove($header);
        }
    }

    /**
     * @var $file
     */
    private static function includeFile($file) {
        if (file_exists($file)) {
            require $file;
            return true;
        } else {
            return false;
        }
    }

    /**
     * get http code to message
     * @param  int $code
     */
    public static function httpStatusCode2Message($code) {
        $http_codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            103 => 'Checkpoint',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        );
    
        if(isset($http_codes[$code])) {
            return $http_codes[$code];
        } else {
            return '';
        }
    }

    
    /**
     * NOTICE: only for development purpose
     * prints the routes
     * @return void
     */
    public static function check_routes() {
        print_r(self::$routes);
    }

    /**
     * NOTICE: only for development purpose
     * prints the pattern routes
     * @return void
     */
    public static function check_pattern_routes() {
        print_r(self::$pattern_routes);
        print_r(self::$route_params);
    }   

    /**
     * NOTICE: only for development purpose
     * prints the route_dirs
     * @return void
     */
    public static function check_route_dirs() {
        print_r(self::$route_dirs);
    }

    /**
     * NOTICE: only for development purpose
     * prints the redirects
     * @return void
     */
    public static function check_redirects() {
        print_r(self::$redirects);
    }

}

