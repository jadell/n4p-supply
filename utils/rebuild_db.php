<?php
require_once(__DIR__.'/../public/neo4jphp.phar');

use Everyman\Neo4j\Client,
    Everyman\Neo4j\Node,
    Everyman\Neo4j\Relationship,
    Everyman\Neo4j\Index\NodeIndex;

$client = new Client();
$ref = $client->getReferenceNode();
$userIndex = new NodeIndex($client, 'USERS');
$groupIndex = new NodeIndex($client, 'GROUPS');

// Remove users if any exist
$userRefRels = $ref->getRelationships('USERS', Relationship::DirectionOut);
foreach ($userRefRels as $userRefRel) {
	$userRef = $userRefRel->getEndNode();
	$userRels = $userRef->getRelationships('USER', Relationship::DirectionOut);
	foreach ($userRels as $userRel) {
		$user = $userRel->getEndNode();
		$groupRels = $user->getRelationships('MEMBER_OF', Relationship::DirectionOut);
		foreach ($groupRels as $groupRel) {
			$groupRel->delete();
		}

		$userRel->delete();
		$user->delete();
	}

	$userRefRel->delete();
	$userRef->delete();
}
$userIndex->delete();

// Remove groups if any exist
$groupRefRels = $ref->getRelationships('GROUPS', Relationship::DirectionOut);
foreach ($groupRefRels as $groupRefRel) {
	$groupRef = $groupRefRel->getEndNode();
	$groupRels = $groupRef->getRelationships('GROUP', Relationship::DirectionOut);
	foreach ($groupRels as $groupRel) {
		$group = $groupRel->getEndNode();
		$subGroupRels = $group->getRelationships('MEMBER_OF');
		foreach ($subGroupRels as $subGroupRel) {
			$subGroupRel->delete();
		}

		$groupRel->delete();
		$group->delete();
	}

	$groupRefRel->delete();
	$groupRef->delete();
}
$groupIndex->delete();

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


