<?php
/*
 LICENSE
 Copyright (c) 2010 Peter Petermann

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.

*/


/* 
    THIS IS AN EXAMPLE! EDIT IT TO SUIT YOUR NEEDS!!! 
    You will have to require/include this from your main config, in order to take effect!
*/


// ensure doctrine is loaded, depending on your doctrine installation (for example through pear) you have to modify this!
require_once(APP_PATH . '/lib/Doctrine/lib/Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));

// registry object
$reg = King23_Registry::getInstance();

// configuration of doctrine
$reg->doctrine = array(
  "manager" => Doctrine_Manager::getInstance(),
  "connections" => array(
    "connection1" => Doctrine_Manager::connection("sqlite:///" . APP_PATH . "/db/db1.sqlite") 
   ),
  "config" => array(
    'data_fixtures_path'  =>  APP_PATH . '/db/data/fixtures',
    'models_path'         =>  APP_PATH . '/db/models',
    'migrations_path'     =>  APP_PATH . '/db/migrations',
    'sql_path'            =>  APP_PATH . '/db/data/sql',
    'yaml_schema_path'    =>  APP_PATH . '/db/schema'
  )
);
// ensure doctrine models are loaded
King23_Classloader::init(APP_PATH . "/db/models");
