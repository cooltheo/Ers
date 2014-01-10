<?php

define('DS', DIRECTORY_SEPARATOR);
define('ERS_BASE_DIR', dirname(__FILE__));
define('ERS_CONFIG_DIR', ERS_BASE_DIR . DS . "conf");
define('ERS_TEMP_DIR', ERS_BASE_DIR. DS . "temp");
define('ERS_SYSTEM_CONFIG_FILE',  "system_config.ini");
define('ERS_TYPE_CONFIG_FILE', "config.ini");
define('ERS_TYPE_MAPPING_FILE', "mapping.ini");

function autoloader($class) {

   $classPath = explode("\\", $class);
   $classPath = array_splice($classPath, 1);
   $classPath = join(DIRECTORY_SEPARATOR, $classPath) . ".php";
   $file = ERS_BASE_DIR .DS. $classPath;
   require $file;
}
spl_autoload_register('autoloader');