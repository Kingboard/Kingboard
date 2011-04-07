<?php

ini_set('include_path', 
        ini_get('include_path').
        PATH_SEPARATOR.
        dirname(__FILE__) . '/../../../../../../usr/share/php'.
        PATH_SEPARATOR.
        dirname(__FILE__). '/../../../../../../usr/share/php5'
);

require_once __DIR__ . '/../lib/King23/lib/core/King23_Classloader.php';
spl_autoload_register("King23_Classloader::load");
require_once __DIR__ . '/../conf/config.php';
