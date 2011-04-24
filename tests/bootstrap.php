<?php

ini_set('include_path', 
        ini_get('include_path').
        PATH_SEPARATOR.
        '/usr/share/php'.
        PATH_SEPARATOR.
        '/usr/share/php5',
        __DIR__ . '/..'
);

require_once 'lib/King23/lib/core/King23_Classloader.php';
spl_autoload_register("King23_Classloader::load");

require_once 'conf/config.php';
