<?php
/*
 MIT License
 Copyright (c) 2011 Peter Petermann

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.

*/
/**
 * Wrapper for mbstring based string functions
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_Helper_String implements King23_Singleton
{
    /**
     * The only instance of the universe
     *
     * @var Kingboard_Helper_String
     */
    protected static $instance = null;

    /**
     * Get the instance
     *
     * @return Kingboard_Helper_String
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Kingboard_Helper_String();
        }
        return self::$instance;
    }

    /**
     * Enforcing the singleton
     */

    final protected function __construct()
    {
        mb_internal_encoding('UTF-8');
    }

    final private function __clone()
    {
    }

    /**
     * Mesure the length of a string
     *
     * @param string $str
     * @return integer
     */
    public function strlen($str) {
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
        if (is_int($length)) {
            return mb_substr($str, $start, $length);
        } else {
            return mb_substr($str, $start);
        }
    }

    /**
     * Convert to lower case
     *
     * @param string $str
     * @return string
     */
    public function lower($str) {
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
    public function strpos($needle, $haystack, $offset = null) {
        if (is_int($offset)) {
            return mb_strpos($haystack, $needle, $offset);
        } else {
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
    public function stripos($needle, $haystack, $offset = null) {
        if (is_int($offset)) {
            return mb_stripos($haystack, $needle, $offset);
        } else {
            return mb_stripos($haystack, $needle);
        }
    }
}
