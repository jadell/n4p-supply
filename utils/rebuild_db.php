<?php
require_once(__DIR__.'/../public/neo4jphp.phar');

use Everyman\Neo4j\Client,
    Everyman\Neo4j\Node,
    Everyman\Neo4j\Relationship,
    Everyman\Neo4j\Index\NodeIndex;

/**
 * Function remove a user node
 *
 * @param Node $user
 */
function removeUser(Node $user)
{
	// $groupRels = $user->getRelationships('IN_GROUP', Relationship::DirectionOut);
	// foreach ($groupRels as $rel) {
	// 	removeGroup($rel->getEndNode());
	// }

	$user->delete();
}

$client = new Client();
$ref = $client->getReferenceNode();
$userIndex = new NodeIndex($client, 'USERS');

// Remove users if any exist
$userRefRels = $ref->getRelationships('USERS', Relationship::DirectionOut);
foreach ($userRefRels as $userRefRel) {
	$userRef = $userRefRel->getEndNode();
	$userRels = $userRef->getRelationships('USER', Relationship::DirectionOut);
	foreach ($userRels as $userRel) {
		$user = $userRel->getEndNode();
		$userRel->delete();
		$user->delete();
	}

	$userRefRel->delete();
	$userRef->delete();
}
$userIndex->delete();

// Recreate out initial data
$userIndex->save();
$userRef = $client->makeNode()->setProperty('name', 'USERS')->save();
$ref->relateTo($userRef, 'USERS')->save();

$usersData = array(
	'josh.adell@gmail.com' => array(
		'email' => 'josh.adell@gmail.com',
		'fullName' => 'Josh Adell',
		'website' => 'http://joshadell.com',
	),
	'testmctestguy@example.com' => array(
		'email' => 'testmctestguy@example.com',
		'fullName' => 'Test McTestguy',
		'website' => 'http://example.com',
	),
);
foreach ($usersData as $userData) {
	$user = $client->makeNode()->setProperties($userData)->save();
	$userRef->relateTo($user, 'USER')->save();
	$userIndex->add($user, 'email', $user->getProperty('email'));
}