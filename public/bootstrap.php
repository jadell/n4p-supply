<?php
require_once(__DIR__.'/silex.phar');
require_once(__DIR__.'/neo4jphp.phar');
require_once(__DIR__.'/JsonResponse.php');
require_once(__DIR__.'/AclService.php');
require_once(__DIR__.'/Formatter.php');

$app = new Silex\Application();
$app['debug'] = true;

// Utility for finding the base url
$app['baseUrl'] = $app->share(function ($app) {
	$request = $app['request'];
	return $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
});

// Neo4j client
$app['neo4j'] = $app->share(function ($app) {
	return new Everyman\Neo4j\Client();
});

// Acl Service
$app['acl'] = $app->share(function ($app) {
	return new AclService($app['neo4j']);
});

// Formatter
$app['formatter'] = $app->share(function ($app) {
	return new Formatter($app);
});
