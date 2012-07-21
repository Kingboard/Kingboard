<?php
/**
 * Wrapper for mbstring based string functions
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_Helper_String extends Kingboard_Helper_Singleton
{

    /**
     * Enforcing the singleton
     */

    protected function __construct()
    {
        mb_internal_encoding('UTF-8');
    }

    /**
     * Mesure the length of a string
     *
     * @param string $str
     * @return integer
     */
    public function strlen($str)
    {
        return mb_strlen($str);
    }

    /**
     * Get a part of a string
     *
     * @param string $str
     * @param integer $start
     * @param integer $length
     * @return string
     */
    public function substr($str, $start, $length = null) {
        if (is_int($length))
        {
            return mb_substr($str, $start, $length);
        }
        else
        {
            return mb_substr($str, $start);
        }
    }

    /**
     * Convert to lower case
     *
     * @param string $str
     * @return string
     */
    public function lower($str)
    {
        return mb_strtolower($str);
    }

    /**
     * Find the needle in the haystack, case - insensitive
     *
     * @param string $needle
     * @param string $haystack
     * @param integer $offset
     * @return integer|boolean
     */
    public function strpos($needle, $haystack, $offset = null)
    {
        if (is_int($offset))
        {
            return mb_strpos($haystack, $needle, $offset);
        }
        else
        {
            return mb_strpos($haystack, $needle);
        }
    }

    /**
     * Find the needle in the haystack, case - sensitive
     *
     * @param string $needle
     * @param string $haystack
     * @param integer $offset
     * @return integer|boolean
     */
    public function stripos($needle, $haystack, $offset = null)
    {
        if (is_int($offset))
        {
            return mb_stripos($haystack, $needle, $offset);
        }
        else
        {
            return mb_stripos($haystack, $needle);
        }
    }
}
