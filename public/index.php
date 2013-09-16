<?php
/**
 * Kingboards King23 Front Controller
 */

// set error reporting on
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_STRICT);

// set APP_PATH
define("APP_PATH", realpath(dirname(__FILE__) . "/.."));

// include config
require_once APP_PATH . "/conf/config.php";

// dispatch request
King23\Core\Router::getInstance()->dispatch($_SERVER["REQUEST_URI"]);
