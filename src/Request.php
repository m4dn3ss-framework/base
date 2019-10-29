<?php

namespace m4dn3ss\framework;

use m4dn3ss\framework\utilities\Purifier;

/**
 * Class Request
 * @package m4dn3ss\framework
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 *
 * @property array $post
 * @property array $get
 * @property string $requestUri
 * @property string $httpHost
 * @property string $requestType
 */

class Request
{
    private $post = [], $get = [], $requestUri = '', $httpHost = '', $requestType = '';

    function __construct()
    {
        $this->loadRequestData();
    }

    /**
     * Returns $_GET parameter by key if it exists.
     * Returns default value if it's not
     * @param $key
     * @param null $defaultValue
     * @return null
     */
    public function getQuery($key, $defaultValue = null)
    {
        return isset($this->get[$key]) ? $this->get[$key] : $defaultValue;
    }

    /**
     * Returns $this->get
     * @return array
     */
    public function getAllQueryParams()
    {
        return $this->get;
    }

    /**
     * Delete parameter from `$this->get` array
     * Despite this parameter still will be available in global `$_GET` array
     * @param $key
     */
    public function unsetQuery($key)
    {
        if(isset($this->get[$key]))
            unset($this->get[$key]);
    }

    /**
     * Returns $_POST parameter by key if it exists.
     * Returns default value if it's not
     * @param $key
     * @param null $defaultValue
     * @return null
     */
    public function getPost($key, $defaultValue = null)
    {
        return isset($this->post[$key]) ? $this->post[$key] : $defaultValue;
    }

    /**
     * Returns $this->post
     * @return array
     */
    public function getAllPostParams()
    {
        return $this->post;
    }

    /**
     * Delete parameter from `$this->post` array
     * Despite this parameter still will be available in global `$_POST` array
     * @param $key
     */
    public function unsetPost($key)
    {
        if(isset($this->post[$key]))
            unset($this->post[$key]);
    }

    /**
     * @param bool $raw
     * @return array|string
     */
    public function getRequestUri($raw = false){
        if($raw) {
            return $this->requestUri;
        }
        return Purifier::sanitize($this->requestUri);
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @return string
     */
    public function getHttpHost()
    {
        return $this->httpHost;
    }

    /**
     * Loads data from $_POST and $_GET global array to $this->post and $this->get
     */
    private function loadRequestData()
    {
        if(isset($_POST)) {
            foreach ($_POST as $k => $v) {
                $k = Purifier::sanitize($k);
                $v = Purifier::sanitize($v);
                if(!empty($k) && !empty($v)) {
                    $this->post[$k] = $v;
                }
            }
        }
        if(isset($_GET)) {
            foreach ($_GET as $k => $v) {
                $k = Purifier::sanitize($k);
                $v = Purifier::sanitize($v);
                if(!empty($k) && !empty($v)) {
                    $this->get[$k] = $v;
                }
            }
        }

        $this->requestUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $this->httpHost = $_SERVER['HTTP_HOST'];
        $this->requestType = strtolower($_SERVER['REQUEST_METHOD']);
    }

}