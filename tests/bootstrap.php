<?php

set_include_path(
        get_include_path().
        PATH_SEPARATOR.
        '/usr/share/php'.
        PATH_SEPARATOR.
        '/usr/share/php5'.
        PATH_SEPARATOR .
        __DIR__ . '/..'
);

require_once 'lib/King23/lib/core/King23_Classloader.php';
King23_Classloader::register();

require_once 'conf/config.php';
