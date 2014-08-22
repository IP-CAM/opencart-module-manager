<?php 


/**
 * All the teil module service providers should be there
 */
$GLOBALS['TeilServiceProviders'] = array();


/**
 * Include autoloader for loading all the application classes
 */
require_once('lib/Autoload.php');


/**
 * Simply load all the files
 */
Teil\Lib\Autoload::start();


/**
 * Startup the application
 */
require_once('startup.php');