<?php
namespace m4dn3ss\framework;

/**
 * Class App
 * @package app
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 *
 * @property Request $request
 * @property Config $config
 * @property Database $db
 *
 */

class App
{
    private static $request = null, $config = null, $db = null, $rootDirectory = null;

    public function __construct($rootDirectory)
    {
    	self::$rootDirectory = $rootDirectory;
    }

    /**
     * @return string
     */
    public static function rootDir()
    {
        return self::$rootDirectory;
    }

    /**
     * @return Request
     */
    public static function request()
    {
        if(self::$request === null)
            self::$request = new Request();

        return self::$request;
    }

    /**
     * @return Config
     */
    public static function config()
    {
        if(self::$config === null)
            self::$config = new Config(self::rootDir() . DIRECTORY_SEPARATOR . 'config');

        return self::$config;
    }

    /**
     * @return Database
     */
    public static function db()
    {
        if(self::$db === null) {
            try {
                self::$db = new Database(self::config()->getParam('db'));
            }
            catch(\Exception $e) {
                exit($e->getMessage());
            }
        }
        return self::$db;
    }

    /**
     * Main function
     * @throws \Exception
     */

    public function run()
    {
        try {
            $action = Router::resolve();
            if ($action && isset($action['function']) && is_callable($action['function'])) {
                $parameters = $action['parameters'] ?? array();
                call_user_func_array($action['function'], $parameters);
            }
        } catch (\Exception $e) {
            call_user_func_array(Router::prepareErrorAction(), ['code' => $e->getCode()]);
        }
    }
}