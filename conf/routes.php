<?php
$router = King23_Router::getInstance();
$router->setBaseHost(King23_Registry::getInstance()->baseHost);

// home
$router->addRoute("/", "Kingboard_Homepage_View", "index", array());
$router->addRoute("/home/page/", "Kingboard_Homepage_View", "index", array('page', 'ajax'));

// information
$router->addRoute("/information", "Kingboard_Information_View", "index");

// pilot details
$router->addRoute("/pilot/", "Kingboard_Homepage_View", "pilot", array('hid'));
$router->addRoute("/pilot/name/", "Kingboard_Search_View", "namePilot", array('pilotname'));

// corporation details
$router->addRoute("/corporation/", "Kingboard_Homepage_View", "corporation", array("hid"));
$router->addRoute('/corporation/name/', "Kingboard_Search_View", "nameCorporation", array("corpname"));

// alliance details
$router->addRoute("/alliance/", "Kingboard_Homepage_View", "alliance", array("hid"));
$router->addRoute('/alliance/name/', "Kingboard_Search_View", "nameAlliance", array("alliancename"));



// kill details
$router->addRoute("/kill/", "Kingboard_Kill_View", 'index', array('killID'));

// registration
$router->addRoute("/user/registration", "Kingboard_Auth_View", 'registerForm');
$router->addRoute("/user/activate/", "Kingboard_Auth_View", 'activateUser',array('activationkey'));

// authentication
$router->addRoute("/login", "Kingboard_Auth_View", "login");
$router->addRoute("/logout", "Kingboard_Auth_View", "logout");

// user specific routes
$router->addRoute("/account/", "Kingboard_User_View", "myKingboard");

// autcompleters
$router->addRoute("/autocomplete/solarsystem", "Kingboard_AutoCompleter_View", 'solarSystem');
$router->addRoute("/autocomplete/region", "Kingboard_AutoCompleter_View", 'region');

// battles
$router->addRoute("/battle/new", "Kingboard_BattleEditor_View", "create");
$router->addRoute("/battle/", "Kingboard_Battle_View", "show", array("id"));