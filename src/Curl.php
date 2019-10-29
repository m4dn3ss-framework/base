<?php

namespace m4dn3ss\framework;

/**
 * Class Curl
 * @package m4dn3ss\framework
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 *
 * @property resource $ch
 * @property mixed $result
 */

class Curl
{
    private $ch, $result;

    /**
     * Curl constructor.
     * Request URL and options can be specified here
     * @param null|string $url
     * @param null|array $options
     */
    public function __construct(
        $url = null,
        $options = [
            CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
            CURLOPT_RETURNTRANSFER => true
        ]
    )
    {
        $this->ch = curl_init();
        if(!is_null($url))
            $this->setOption(CURLOPT_URL, $url);
        if(!is_null($options)) {
            foreach ($options as $key => $value) {
                $this->setOption($key, $value);
            }
        }
    }

    /**
     * Executes current cURL
     */
    public function send()
    {
        $this->result = curl_exec($this->ch);
    }

    /**
     * Returns response
     * @return null
     */
    public function getResponse()
    {
        return $this->result;
    }

    /**
     * Returns errors if they exists, else returns null
     * @return null|string
     */
    public function getErrors()
    {
        if(curl_errno($this->ch))
            return curl_error($this->ch);
        return null;
    }

    /**
     * Set cookies for request
     * @param $array
     */
    public function setCookies($array)
    {
        $cookiesString = '';
        foreach ($array as $key => $value) {
            $cookiesString .= $key . '=' . $value . ';';
        }
        if(!empty($cookiesString))
            $this->setOption(CURLOPT_COOKIE, $cookiesString);
    }

    /**
     * Sets post data for request
     * @param $data
     */
    public function setFormData($data)
    {
        $this->setOption(CURLOPT_POSTFIELDS, $data);
    }

    /**
     * Sets cUrl option
     * @param $option
     * @param $value
     */
    public function setOption($option, $value)
    {
        curl_setopt($this->ch, $option, $value);
    }

}