<?php
require_once(__DIR__.'/bootstrap.php');

// Root defines our available endpoints
$app->get('/', function () use ($app) {
	return new JsonResponse(array(
		'user' => $app['baseUrl'].'/user',
		'group' => $app['baseUrl'].'/group',
	));
});

// List all users
$app->get('/user', function () use ($app) {
	$users = array();
	foreach ($app['acl']->getUsers() as $user) {
		$users[] = $app['formatter']->formatUser($user);
	}

	return new JsonResponse($users);
});

// Single user
$app->get('/user/{id}', function ($id) use ($app) {
	$user = $app['acl']->getUser($id);
	if (!$user) {
		return new JsonResponse($user, 404);
	}
	
	$user = $app['formatter']->formatUser($user);
	return new JsonResponse($user);
});

// Groups for a single user
$app->get('/user/{id}/groups', function ($id) use ($app) {
	$direct = (boolean)$app['request']->get('direct');

	$groups = array();
	foreach ($app['acl']->getUserGroups($id, !$direct) as $group) {
		$groups[] = $app['formatter']->formatGroup($group);
	}

	return new JsonResponse($groups);
});

// Permissions for a single user
$app->get('/user/{id}/permissions', function ($id) use ($app) {
	$perms = array();
	foreach ($app['acl']->getUserPermissions($id) as $perm) {
		$perms[] = $app['formatter']->formatPermission($perm);
	}

	return new JsonResponse($perms);
});

// Permissions for a single user
$app->get('/user/{id}/permissions/{permission}', function ($id, $permission) use ($app) {
	if ($app['acl']->doesUserHavePermission($id, $permission)) {
		return new JsonResponse(null, 204);
	} else {
		return new JsonResponse(null, 404);
	}
});

// List all groups
$app->get('/group', function () use ($app) {
	$groups = array();
	foreach ($app['acl']->getGroups() as $group) {
		$groups[] = $app['formatter']->formatGroup($group);
	}

	return new JsonResponse($groups);
});

// Single group
$app->get('/group/{id}', function ($id) use ($app) {
	$group = $app['acl']->getGroup($id);
	if (!$group) {
		return new JsonResponse($group, 404);
	}

	$group = $app['formatter']->formatGroup($group);
	return new JsonResponse($group);
});

// List all members in a group
$app->get('/group/{id}/members', function ($id) use ($app) {
	$direct = (boolean)$app['request']->get('direct');

	$members = array();
	foreach ($app['acl']->getGroupMembers($id, !$direct) as $member) {
		if (isset($member['email'])) {
			$members[] = $app['formatter']->formatUser($member);
		} else {
			$members[] = $app['formatter']->formatGroup($member);
		}
	}

	return new JsonResponse($members);
});

// Permissions for a single group
$app->get('/group/{id}/permissions', function ($id) use ($app) {
	$perms = array();
	foreach ($app['acl']->getGroupPermissions($id) as $perm) {
		$perms[] = $app['formatter']->formatPermission($perm);
	}

	return new JsonResponse($perms);
});

$app->run();
