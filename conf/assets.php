<?php
$assets = array();
$assets['css'] = array();
$assets['js'] = array();
$assets['fonts'] = array();

// css files
$assets['css'][] = APP_PATH . "/vendor/twbs/bootstrap/dist/css/bootstrap.css";
$assets['css'][] = APP_PATH . "/public/css/main.css";

// js files
$assets['js'][] = APP_PATH . "/vendor/frameworks/jquery/jquery.js";
$assets['js'][] = APP_PATH . "/vendor/twbs/bootstrap/dist/js/bootstrap.min.js";
$assets['js'][] = APP_PATH . "/public/js/common.js";

$assets['fonts']["glyphicons-halflings-regular.eot"]
    = APP_PATH . '/vendor/twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.eot';
$assets['fonts']["glyphicons-halflings-regular.svg"]
    = APP_PATH . '/vendor/twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.svg';
$assets['fonts']["glyphicons-halflings-regular.ttf"]
    = APP_PATH . '/vendor/twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.ttf';
$assets['fonts']["glyphicons-halflings-regular.woff"]
    = APP_PATH . '/vendor/twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.woff';


\King23\Core\Registry::getInstance()->assets = $assets;
