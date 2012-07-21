<?php
/**
 * Abstract class for easier implementation of singletons
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
abstract class Kingboard_Helper_Singleton implements King23_Singleton {

    protected static $instance;
    
    /**
     * Implement the singleton
     * 
     * @return Kingboard_Helper_Singleton 
     */
    public static function getInstance()
    {
        if (!static::$instance)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    /**
     * Enforce the singleton
     */
    
    protected function __construct() {}
    protected function __clone() {}
}
