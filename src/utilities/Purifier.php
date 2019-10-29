<?php

namespace m4dn3ss\framework\utilities;

/**
 * Class Purifier
 * @package m4dn3ss\framework\utilities
 *
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 */
class Purifier
{
    /**
     * Sanitize given value from forbidden characters
     *
     * @param $value
     * @param bool $trim
     * @param bool $stripTags
     * @param bool $stripSlashes
     * @return array|mixed|string
     */
    public static function sanitize($value, $trim = true, $stripTags = true, $stripSlashes = true)
    {
        if(is_array($value)) {
            $post = array();
            foreach ($value as $key => $value) {
                $post[$key] = self::sanitize($value, $trim, $stripTags, $stripSlashes);
            }
            return $post;
        }
        else {
            if($trim) {
                $value = trim($value);
            }
            if($stripTags) {
                $value = strip_tags($value);
            }
            if($stripSlashes) {
                $value = stripslashes($value);
            }
            return $value;
        }
    }
}