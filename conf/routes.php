<?php
/**
 * Kingboards King23 Routing configuration, usually you should not need to touch this.
 */

// basic router configuration
$router =  \King23\Core\Router::getInstance();
$router->setBaseHost(\King23\Core\Registry::getInstance()->baseHost);

// home
$router->addRoute("/", 'Kingboard\Views\Homepage', "newIndex", array());
//$router->addRoute("/", 'Kingboard\Views\Date', 'index', array('date', 'page'));
$router->addRoute("/top/value", 'Kingboard\Views\Homepage', 'topValue');
$router->addRoute("/top/killer", 'Kingboard\Views\Homepage', 'topKiller');
$router->addRoute("/top/loser", 'Kingboard\Views\Homepage', 'topLoser');

// information
$router->addRoute("/information", 'Kingboard\Views\Information', "index");

// assetic
$router->addRoute("/assets/js/", 'Kingboard\Views\Assetic', "js");
$router->addRoute("/assets/css/", 'Kingboard\Views\Assetic', "css");
$router->addRoute("/assets/fonts/", 'Kingboard\Views\Assetic', 'fonts', array('font'));
$router->addRoute("/assets/background/", 'Kingboard\Views\Assetic', 'background');

// eve information
$router->addRoute("/eveinfo/", 'Kingboard\Views\EveInfo', "eveItem", array('itemid'));

$router->addRoute("/day/", 'Kingboard\Views\Date', 'index', array('date', 'page'));

// url search
$router->addRoute('/faction/name/', 'Kingboard\Views\Search', "nameFaction", array("factionname"));
$router->addRoute("/pilot/name/", 'Kingboard\Views\Search', "namePilot", array('pilotname'));
$router->addRoute('/corporation/name/', 'Kingboard\Views\Search', "nameCorporation", array("corpname"));
$router->addRoute('/alliance/name/', 'Kingboard\Views\Search', "nameAlliance", array("alliancename"));

// corp/alliance/faction/pilot statistics
$router->addRoute("/details/", 'Kingboard\Views\Homepage', "killlist", array("ownerType", "ownerID","page"));

// kills index
$router->addRoute("/kills/", 'Kingboard\Views\Kill', 'index', array('page'));

// kill details
$router->addRoute("/kill/", 'Kingboard\Views\Kill', 'details', array('killID'));
$router->addRoute("/killjson/", 'Kingboard\Views\Kill', 'json', array('killID'));

// authentication related routes
if(!is_null($reg->auth) && $reg->auth)
    $auth = $reg->auth;
else
    $auth = 'Kingboard\Views\Auth\Auth';

// registration
$router->addRoute("/user/registration", $auth, 'registerForm');
$router->addRoute("/user/activate/", $auth, 'activateUser', array('activationkey'));

// authentication
$router->addRoute("/login", $auth, "login");
$router->addRoute("/logout", $auth, "logout");

// oAuthentication
$router->addRoute("/oauth2/callback/", 'Kingboard\Views\Auth\OAuth2', "callback", array("key"));

// user specific routes
$router->addRoute("/account/", 'Kingboard\Views\User', "myKingboard");
$router->addRoute("/account/api/delete/", 'Kingboard\Views\User', 'delete', array('xsrf', 'keyid'));

// autcompleters
$router->addRoute("/autocomplete/solarsystem/", 'Kingboard\Views\AutoCompleter', 'solarSystem', array("term"));
$router->addRoute("/autocomplete/region/", 'Kingboard\Views\AutoCompleter', 'region', array("term"));
$router->addRoute("/autocomplete/search/", 'Kingboard\Views\AutoCompleter', "search", array("text"));

// battles
$router->addRoute("/battles/", 'Kingboard\Views\Battle', "index", array("page"));

$router->addRoute("/battle/editor", 'Kingboard\Views\BattleEditor', "index");
$router->addRoute("/battle/new", 'Kingboard\Views\BattleEditor', "create");
$router->addRoute("/battle/", 'Kingboard\Views\Battle', "show", array("id"));

// search
$router->addRoute("/search/", 'Kingboard\Views\Search', "index");
