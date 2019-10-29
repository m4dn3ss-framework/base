<?php

namespace m4dn3ss\framework;

use m4dn3ss\App;
use m4dn3ss\framework\exceptions\NotFoundException;

/**
 * Class Router
 * @package m4dn3ss\framework
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 */
class Router
{
    private static $getPatterns = array();
    private static $postPatterns = array();
    private static $putPatterns = array();
    private static $deletePatterns = array();
    private static $routes = array();

    /**
     * @param $pattern
     * @param $action
     */
    public static function get($pattern, $action)
    {
        self::$getPatterns[$pattern] = $action;
    }

    /**
     * @param $pattern
     * @param $action
     */
    public static function post($pattern, $action)
    {
        self::$postPatterns[$pattern] = $action;
    }

    /**
     * @param $pattern
     * @param $action
     */
    public static function put($pattern, $action)
    {
        self::$putPatterns[$pattern] = $action;
    }

    /**
     * @param $pattern
     * @param $action
     */
    public static function delete($pattern, $action)
    {
        self::$deletePatterns[$pattern] = $action;
    }

    /**
     * @return array
     * @throws NotFoundException
     */
    public static function resolve()
    {
        self::$routes = self::getRoutes();
        $requestType = App::request()->getRequestType();
        $patterns = array();
        switch($requestType) {
            case 'post':
                $patterns = self::$postPatterns;
                break;
            case 'get':
                $patterns = self::$getPatterns;
                break;
            case 'put':
                $patterns = self::$putPatterns;
                break;
            case 'delete':
                $patterns = self::$deletePatterns;
                break;
        }

        $preparedPatterns = array();
        foreach ($patterns as $pattern => $action) {
            $preparedPattern = self::preparePattern($pattern);
            $preparedAction = self::prepareAction($action, App::config()->getParam('controllers:namespace'));
            if (!empty($preparedPattern) && !empty($preparedAction)) {
                $preparedPatterns[] = array_merge($preparedPattern, array('action' => $preparedAction));
            }
        }

        if (!empty($preparedPatterns)) {
            foreach ($preparedPatterns as $pattern) {
                $patternRoutes = $pattern['routes'] ?? null;
                $patternVariables = $pattern['variables'] ?? null;
                $patternRoutesCount = 0;
                if ($patternRoutes) {
                    $patternRoutesCount += count($patternRoutes);
                }
                if ($patternVariables) {
                    $patternRoutesCount += count($patternVariables);
                }
                $returnThis = true;
                $parameters = array();
                if (count(self::$routes) == $patternRoutesCount) {
                    foreach (self::$routes as $position => $route) {
                        if (!isset($patternRoutes[$position]) && !isset($patternVariables[$position])) {
                            $returnThis = false;
                        }

                        if (isset($patternRoutes[$position]) && $patternRoutes[$position] != $route) {
                            $returnThis = false;
                        }

                        if (isset($patternVariables[$position])) {
                            $parameters[$patternVariables[$position]] = $route;
                        }
                    }

                    if ($returnThis) {
                        return array(
                            'function' => $pattern['action'],
                            'parameters' => $parameters
                        );
                    }
                }
            }
        }

        throw new NotFoundException();
    }

    /**
     * @return array|string
     */
    private static function getRoutes()
    {
        $requestUri = App::request()->getRequestUri(true);
        if ($requestUri == '/') {
            return array('/');
        }
        $httpHost = App::request()->getHttpHost();
        if (stripos($requestUri, 'index') || stripos($requestUri, 'index.php') || stripos($requestUri, 'index.html')) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: //".$httpHost.'/');
            exit();
        }
        if (stripos($requestUri, '//') === false) {
            $routes = explode('?', $requestUri);
            $routes = $routes[0];
            $routes = explode('/', trim($routes, '/'));
            // looking for forbidden elements in array and clean it
            $tmpRoutes = array();
            foreach ($routes as $k => $v) {
                $v = self::clean($v);
                $tmpRoutes[] = $v;
            }
            return $tmpRoutes;
        } else {
            $routes = explode('?', $requestUri);
            $routes = $routes[0];
            $routes = explode('/', trim($routes, '/'));
            // looking for forbidden elements in array and clean it
            $tmpRoutes = array();
            foreach ($routes as $k => $v) {
                $v = self::clean($v);
                if(!empty($v)) {
                    $tmpRoutes[] = $v;
                }
            }
            $tmpRoutes = implode('/', $tmpRoutes);
            self::redirect("//".$httpHost.'/'.$tmpRoutes);
        }
    }

    /**
     * @param $address
     */
    private static function redirect($address)
    {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $address");
        exit();
    }

    /**
     * @param $v
     * @return string|string[]|null
     */
    private static function clean($v)
    {
        return preg_replace("/[^A-Za-z0-9_\-?!]/",'',$v);
    }

    /**
     * @param $pattern
     * @return array
     */
    private static function preparePattern($pattern)
    {
        if ($pattern == '/') {
            return array('routes' => array(0 => '/'));
        }
        $exploded = explode('/', trim($pattern, '/'));
        $return = array();
        if (!empty($exploded)) {
            $routes = array();
            $variables = array();
            foreach ($exploded as $position => $part) {
                if (!empty($part)) {
                    // checks if this is variable
                    if (strpos($part, '{') === 0 && strpos($part, '}') === (strlen($part) - 1)) {
                        $varName = str_replace(['{', '}'], '', $part);
                        $variables[$position] = $varName;
                    } else {
                        $routes[$position] = $part;
                    }
                }
            }
            $return = [
                'routes' => $routes,
                'variables' => $variables
            ];
        }
        return $return;
    }

    /**
     * @param $action
     * @param null $namespace
     * @return \Closure|null
     */
    private static function prepareAction($action, $namespace = null)
    {
        if (is_callable($action)) {
            return \Closure::fromCallable($action);
        }

        if (is_string($action)) {
            $exploded = explode('@', $action);
            if (count($exploded) == 2) {
                $controllerClass = (!is_null($namespace) ? $namespace : 'app\\controllers') . '\\' . trim($exploded[0]);
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    $methodName = trim($exploded[1]);
                    if (method_exists($controller, $methodName)) {
                        return \Closure::fromCallable([$controller, $methodName]);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return \Closure
     * @throws \Exception
     */
    public static function prepareErrorAction()
    {
        $errorController = App::config()->getParam('controllers:errorController') ?? 'ErrorController';
        $namespace = App::config()->getParam('controllers:namespace') ?? 'app\\controllers';
        $controllerClass = $namespace . '\\' . $errorController;
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, 'index')) {
                return \Closure::fromCallable([$controller, 'index']);
            }
        }
    }

}