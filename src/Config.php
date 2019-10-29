<?php

namespace m4dn3ss\framework;

use m4dn3ss\App;

/**
 * Class Config
 * @package m4dn3ss\framework
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 *
 * @property array $parameters
 */

class Config {

    private static $parameters = null, $configDirectory = null;

    public function __construct($configDirectory)
    {
    	if (self::$configDirectory === null) {
    		self::$configDirectory = $configDirectory;
	    }
        if (self::$parameters === null) {
            $this->loadParameters();
        }
    }

    public function getParam($key, $searchAt = null)
    {
        $parameters = self::$parameters;
        if(!empty($searchAt)) {
            if(!is_array($searchAt))
                throw new \Exception('Parameter `$searchAt` should be array');
            $parameters = $searchAt;
        }

        $keysChain = explode(':', $key);
        if(count($keysChain) > 1) {
            $key = $keysChain[0];
            unset($keysChain[0]);
            if(isset($parameters[$key]) && is_array($parameters[$key]))
                return $this->getParam(implode(':', array_values($keysChain)), $parameters[$key]);
        }

        if(isset($parameters[$key]))
            return $parameters[$key];

        return null;
    }

    private function loadParameters()
    {
        $paramsFile = self::$configDirectory . '/main.php';
        if (file_exists($paramsFile)) {
            self::$parameters = include($paramsFile);
        }
    }
}
