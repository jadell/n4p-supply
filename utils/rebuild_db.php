<?php
require_once(__DIR__.'/../public/neo4jphp.phar');

use Everyman\Neo4j\Client,
    Everyman\Neo4j\Cypher\Query,
    Everyman\Neo4j\Node,
    Everyman\Neo4j\Relationship,
    Everyman\Neo4j\Index\NodeIndex;

$client = new Client();
$ref = $client->getReferenceNode();
$userIndex = new NodeIndex($client, 'USERS');
$groupIndex = new NodeIndex($client, 'GROUPS');
$permIndex = new NodeIndex($client, 'PERMISSIONS');

// Order matters here!
$cleanupQueries = array(
	"START z=node(0) MATCH (z)-[:USERS]->()-[:USER]->(n) RETURN n",
	"START z=node(0) MATCH (z)-[:GROUPS]->()-[:GROUP]->(n) RETURN n",
	"START z=node(0) MATCH (z)-[:PERMISSIONS]->()-[:PERMISSION]->(n) RETURN n",
	"START z=node(0) MATCH (z)-[:USERS]->(n) RETURN n",
	"START z=node(0) MATCH (z)-[:GROUPS]->(n) RETURN n",
	"START z=node(0) MATCH (z)-[:PERMISSIONS]->(n) RETURN n",
);

foreach ($cleanupQueries as $cypher) {
	$query = new Query($client, $cypher);
	$results = $query->getResultSet();
	foreach ($results as $result) {
		$node = $result['n'];
		foreach ($node->getRelationships() as $rel) {
			$rel->delete();
		}
		$node->delete();
	}
}

$userIndex->delete();
$groupIndex->delete();
$permIndex->delete();

// GROUP DATA
$groupsData = array(
	'Admins' => array(
		'name' => 'Admins',
		'description' => 'System super users, with all permissions',
	),
	'Readers' => array(
		'name' => 'Readers',
		'description' => 'Users who can read data',
	),
	'Writers' => array(
		'name' => 'Writers',
		'description' => 'Users who can write data',
	),
	'Auditors' => array(
		'name' => 'Auditors',
		'description' => 'Users who can read all data and mark some for review',
	),
);
$groupIndex->save();
$groupRef = $client->makeNode()->setProperty('name', 'GROUPS')->save();
$ref->relateTo($groupRef, 'GROUPS')->save();
foreach ($groupsData as &$groupData) {
	$group = $client->makeNode()->setProperties($groupData)->save();
	$groupRef->relateTo($group, 'GROUP')->save();
	$groupIndex->add($group, 'name', $group->getProperty('name'));

	$groupData['ref'] = $group;
}

$groupsData['Admins']['ref']->relateTo($groupsData['Readers']['ref'], 'MEMBER_OF')->save();
$groupsData['Admins']['ref']->relateTo($groupsData['Writers']['ref'], 'MEMBER_OF')->save();
$groupsData['Admins']['ref']->relateTo($groupsData['Auditors']['ref'], 'MEMBER_OF')->save();
$groupsData['Writers']['ref']->relateTo($groupsData['Readers']['ref'], 'MEMBER_OF')->save();
$groupsData['Auditors']['ref']->relateTo($groupsData['Readers']['ref'], 'MEMBER_OF')->save();

// PERMISSIONS DATA
$permsData = array(
	'edit-users' => array(
		'name' => 'edit-users',
		'description' => 'Create and edit users, change user group assignments',
	),
	'view-users' => array(
		'name' => 'view-users',
		'description' => 'View user data, groups and permissions',
	),
	'edit-groups' => array(
		'name' => 'edit-groups',
		'description' => 'Create and edit groups, add and remove users from groups',
	),
	'view-groups' => array(
		'name' => 'view-groups',
		'description' => 'View group data, group membership and permissions',
	),
	'edit-permissions' => array(
		'name' => 'edit-permissions',
		'description' => 'Create and edit permissions',
	),
	'grant-permissions' => array(
		'name' => 'grant-permissions',
		'description' => 'Grant permissions to users and groups',
	),
	'mark-questionable' => array(
		'name' => 'mark-questionable',
		'description' => 'Mark a permission grant as questionable',
	),
);
$permIndex->save();
$permRef = $client->makeNode()->setProperty('name', 'PERMISSIONS')->save();
$ref->relateTo($permRef, 'PERMISSIONS')->save();
foreach ($permsData as &$permData) {
	$perm = $client->makeNode()->setProperties($permData)->save();
	$permRef->relateTo($perm, 'PERMISSION')->save();
	$permIndex->add($perm, 'name', $perm->getProperty('name'));

	$permData['ref'] = $perm;
}

$groupsData['Admins']['ref']->relateTo($permsData['edit-users']['ref'], 'CAN')->save();
$groupsData['Admins']['ref']->relateTo($permsData['edit-groups']['ref'], 'CAN')->save();
$groupsData['Admins']['ref']->relateTo($permsData['edit-permissions']['ref'], 'CAN')->save();
$groupsData['Admins']['ref']->relateTo($permsData['mark-questionable']['ref'], 'CAN')->save();
$groupsData['Readers']['ref']->relateTo($permsData['view-users']['ref'], 'CAN')->save();
$groupsData['Readers']['ref']->relateTo($permsData['view-groups']['ref'], 'CAN')->save();
$groupsData['Auditors']['ref']->relateTo($permsData['mark-questionable']['ref'], 'CAN')->save();


// USER DATA
$usersData = array(
	'josh.adell@gmail.com' => array(
		'email' => 'josh.adell@gmail.com',
		'name' => 'Josh Adell',
		'website' => 'http://joshadell.com',
	),
	'testmctestguy@example.com' => array(
		'email' => 'testmctestguy@example.com',
		'name' => 'Test McTestguy',
		'website' => 'http://example.com',
	),
	'teveryman@everymansoftware.com' => array(
		'email' => 'teveryman@everymansoftware.com',
		'name' => 'Todd Everyman',
		'website' => 'http://everymansoftware.com',
	),
	'joeuser@example.com' => array(
		'email' => 'joeuser@example.com',
		'name' => 'Joseph User',
		'website' => 'http://example.com',
	),
);
$userIndex->save();
$userRef = $client->makeNode()->setProperty('name', 'USERS')->save();
$ref->relateTo($userRef, 'USERS')->save();
foreach ($usersData as &$userData) {
	$user = $client->makeNode()->setProperties($userData)->save();
	$userRef->relateTo($user, 'USER')->save();
	$userIndex->add($user, 'email', $user->getProperty('email'));

	$userData['ref'] = $user;
}

$usersData['josh.adell@gmail.com']['ref']->relateTo($groupsData['Admins']['ref'], 'MEMBER_OF')->save();
$usersData['testmctestguy@example.com']['ref']->relateTo($groupsData['Auditors']['ref'], 'MEMBER_OF')->save();
$usersData['teveryman@everymansoftware.com']['ref']->relateTo($groupsData['Writers']['ref'], 'MEMBER_OF')->save();
$usersData['joeuser@example.com']['ref']->relateTo($groupsData['Readers']['ref'], 'MEMBER_OF')->save();

$usersData['josh.adell@gmail.com']['ref']->relateTo($permsData['grant-permissions']['ref'], 'CAN')->save();

