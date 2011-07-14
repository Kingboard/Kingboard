<?php
if(!defined("APP_PATH"))
    define("APP_PATH", realpath(dirname(__FILE__) . "/.."));

King23_Classloader::init(APP_PATH . "/lib/King23/lib");
King23_Classloader::init(APP_PATH . "/views");
King23_Classloader::init(APP_PATH . "/lib/Kingboard");
King23_Classloader::init(APP_PATH . "/lib/PHPMarkdown");
King23_Classloader::init(APP_PATH . "/model");

$reg = King23_Registry::getInstance();

// set this to your host
$reg->baseHost = "kings-of-eve.com";

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
    'pluginsPaths'           => array(APP_PATH . "/lib/Kingboard/SithPlugin/"),
//    'recompilationMode'      => 1,
    'recompilationMode'      => 1,
    'defaultIODriver'        => "file",
    'autoEscape'             => false,
));

$reg->imagePaths = array(
    'ships' => 'http://image.eveonline.com/Render/',
    'items' => 'http://image.eveonline.com/Type/',
    'characters' => 'http://image.eveonline.com/Character/',
    'corporations' => 'http://image.eveonline.com/Corporation/',
    'alliances' => 'http://image.eveonline.com/Alliance/'
);

$reg->apimailreceiver = "CHARACTERNAME";
$reg->apimailreceiverCharacterID = 123456;
$reg->apimailreceiverApiUserID = "123456";
$reg->apimailreceiverApiKey = "APIKEYHERE";

// this can be fetched from a specific ownerID provider
// which maps host to id for example on hosted boards
$reg->ownerID = 99000289;

require_once("routes.php");

session_start();