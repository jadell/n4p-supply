<?php
require_once(__DIR__.'/bootstrap.php');

// Root defines our available endpoints
$app->get('/', function () use ($app) {
	return new JsonResponse(array(
		'user' => $app['baseUrl'].'/user',
	));
});

// List all users
$app->get('/user', function () use ($app) {
	$users = array();
	foreach ($app['acl']->getUsers() as $id => $user) {
		$users[] = array(
			'uri' => $app['baseUrl'].'/user/'.rawurlencode($id),
			'email' => $user['email'],
		);
	}

	return new JsonResponse($users);
});

// Single user
$app->get('/user/{id}', function ($id) use ($app) {
	$user = $app['acl']->getUser($id);
	if (!$user) {
		return new JsonResponse($user, 404);
	}

	$user['uri'] = $app['baseUrl'].'/user/'.rawurlencode($id);
	$user['groups'] = $user['uri'].'/groups';
	$user['permissions'] = $user['uri'].'/permissions';
	return new JsonResponse($user);
});

$app->run();
