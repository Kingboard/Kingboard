<?php
if(!defined("APP_PATH"))
    define("APP_PATH", realpath(dirname(__FILE__) . "/.."));

King23_Classloader::init(APP_PATH . "/lib/King23/lib");
King23_Classloader::init(APP_PATH . "/views");
King23_Classloader::init(APP_PATH . "/lib/Kingboard");
$reg = King23_Registry::getInstance();

$connection = new Mongo('localhost');
$reg->mongo = array(
    'connection' => $connection,
    'db' => $connection->Kingboard
);

King23_Classloader::init(APP_PATH . "/lib/Pheal");

$pc = PhealConfig::getInstance();
$pc->cache = new PhealFileCache(APP_PATH . "/cache/");


// Sith Template configuration
require_once(APP_PATH ."/lib/SithTemplate/lib/SithTemplate.php");
$reg->sith = new TemplateEnviron(array(
    'inputPrefix'            => APP_PATH . "/templates/",
    'outputPrefix'           => APP_PATH . "/templates_c/",
    'loadPlugins'            => true,
    'useDefaultPluginsPath'  => true,
    'recompilationMode'      => 1,
    'defaultIODriver'        => "file",
    'autoEscape'             => false,
));


require_once("routes.php");

// uncomment the next line if you have Doctrine installed, and configured in the doctrine.php
//require_once("doctrine.php");
