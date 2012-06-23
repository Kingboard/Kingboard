<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define("APP_PATH", realpath(dirname(__FILE__) . "/.."));

require_once APP_PATH . "/conf/config.php";
King23_Router::getInstance()->dispatch($_SERVER["REQUEST_URI"]);
