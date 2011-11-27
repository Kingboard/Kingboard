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
$pc->http_timeout = 40;

// Twig Template configuration
require_once(APP_PATH . "/lib/King23/external/Twig/lib/Twig/Autoloader.php");
Twig_Autoloader::register();
$reg->twig = new Twig_Environment(new Twig_Loader_Filesystem(APP_PATH ."/templates"), array(
    "cache" => APP_PATH . "/templates_c",
    "auto_reload" => true
));


$reg->imagePaths = array(
    'ships' => 'http://image.eveonline.com/Render/',
    'items' => 'http://image.eveonline.com/Type/',
    'characters' => 'http://image.eveonline.com/Character/',
    'corporations' => 'http://image.eveonline.com/Corporation/',
    'alliances' => 'http://image.eveonline.com/Alliance/'
);

$reg->apimailreceiver = "CHARACTERNAME";
$reg->apimailreceiverCharacterID = 12345;
$reg->apimailreceiverApiUserID = "12345";
$reg->apimailreceiverApiKey = "abcd"; 

// this can be fetched from a specific ownerID provider
// which maps host to id for example on hosted boards
$reg->ownerID = 99000289;
//$reg->ownerID = false;
require_once("routes.php");
MongoCursor::$timeout = -1;
session_start();
