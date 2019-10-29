<?php

namespace m4dn3ss\framework;

/**
 * Class ConsoleCommand
 * @package m4dn3ss\framework
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 *
 * @property array $options
 * @property Config $config
 * @property Database $db
 */
class ConsoleCommand
{
    private $db, $config;
    protected $options;

    public function __construct($options = null)
    {
        $this->options = $this->parseOptions($options);
        $this->config = new Config();
        try {
            $this->db = new Database($this->config->getParam('db'));
        }
        catch(\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * @param $name
     * @return null
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * @param $options
     * @return array
     */
    private function parseOptions($options)
    {
        $return = array();
        if($options && is_array($options) && count($options) > 0) {
            foreach ($options as $option) {
                if(strpos($option, '--') !== false) {
                    $option = $this->parseOption($option);
                    $return = array_merge($return, $option);
                }
            }
        }
        return $return;
    }

    /**
     * @param $option
     * @return array|null
     */
    private function parseOption($option)
    {
        $option = str_replace('--', '', $option);
        $option = explode('=', $option);
        $key = isset($option[0]) && !empty($option[0]) ? $option[0] : null;
        if($key) {
            return array($key => isset($option[1]) && !empty($option[1]) ? $option[1] : null);
        }
        return null;
    }
}