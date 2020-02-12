<?php

/**
 * This is A Development Application entry point - 
 * NOT SUTIBLE FOR PRODUCTION ENVIRNOMENT
 * 
 * Since I am on docker environment requests ip address is different than local host, therefore 
 * there is no need to blacklist any other ip than localhost.
 *
 * This file is equivelent to app_dev however without IP Restrictions.
 * 
 * Also, special note the Docker Nginx serves this file.
 */

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';
Debug::enable();
if (PHP_VERSION_ID < 70000) {
	include_once __DIR__ . '/../var/bootstrap.php.cache';
}

$kernel = new AppKernel('dev', true);
if (PHP_VERSION_ID < 70000) {
	$kernel->loadClassCache();
}
// $kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
// Request::enableHttpMethodParameterOverride();
$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
