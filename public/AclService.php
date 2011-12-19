<?php
use Everyman\Neo4j\Client,
    Everyman\Neo4j\Cypher\Query,
    Everyman\Neo4j\Relationship,
    Everyman\Neo4j\Node,
    Everyman\Neo4j\Index\NodeIndex;

class AclService
{
	protected $client;

	/**
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Get a single group
	 *
	 * @param string $id
	 * @return array
	 */
	public function getGroup($id)
	{
		$cypher = "START g=node:GROUPS(name={name}) RETURN g";
		$query = new Query($this->client, $cypher, array('name' => $id));
		$results = $query->getResultSet();

		if (count($results)>0) {
			return $results[0]['g']->getProperties();
		}
		return null;
	}

	/**
	 * List all members for a group
	 *
	 * @param string $id
	 * @param boolean $recurse
	 * @return array
	 */
	public function getGroupMembers($id, $recurse=true)
	{
		$depth = $recurse ? '' : '1';
		$cypher = "START g=node:GROUPS(name={name}) MATCH (g)<-[:MEMBER_OF*1..$depth]-(m) RETURN distinct m";
		$query = new Query($this->client, $cypher, array('name' => $id));
		$results = $query->getResultSet();

		$members = array();
		foreach ($results as $result) {
			$members[] = $result['m']->getProperties();
		}
		return $members;
	}

	/**
	 * List all permissions for a group
	 *
	 * @param string $id
	 * @return array
	 */
	public function getGroupPermissions($id)
	{
		$cypher = "START g=node:GROUPS(name={name}) MATCH (g)-[:MEMBER_OF*0..]->()-[:CAN]->(p) RETURN distinct p";
		$query = new Query($this->client, $cypher, array('name' => $id));
		$results = $query->getResultSet();

		$perms = array();
		foreach ($results as $result) {
			$perms[] = $result['p']->getProperties();
		}
		return $perms;
	}

	/**
	 * List all groups
	 *
	 * @return array
	 */
	public function getGroups()
	{
		$cypher = 'START g=node:GROUPS("name:*") RETURN g';
		$query = new Query($this->client, $cypher);
		$results = $query->getResultSet();

		$groups = array();
		foreach ($results as $result) {
			$groups[] = $result['g']->getProperties();
		}
		return $groups;
	}

	/**
	 * Get a single user
	 *
	 * @param string $id
	 * @return array
	 */
	public function getUser($id)
	{
		$cypher = "START u=node:USERS(email={email}) RETURN u";
		$query = new Query($this->client, $cypher, array('email' => $id));
		$results = $query->getResultSet();

		if (count($results)>0) {
			return $results[0]['u']->getProperties();
		}
		return null;
	}

	/**
	 * List all groups for a user
	 *
	 * @param string $id
	 * @param boolean $recurse
	 * @return array
	 */
	public function getUserGroups($id, $recurse=true)
	{
		$depth = $recurse ? '' : '1';
		$cypher = "START u=node:USERS(email={email}) MATCH (u)-[:MEMBER_OF*1..$depth]->(g) RETURN distinct g";
		$query = new Query($this->client, $cypher, array('email' => $id));
		$results = $query->getResultSet();

		$groups = array();
		foreach ($results as $result) {
			$groups[] = $result['g']->getProperties();
		}
		return $groups;
	}

	/**
	 * List all permissions for a user
	 *
	 * @param string $id
	 * @return array
	 */
	public function getUserPermissions($id)
	{
		$cypher = "START u=node:USERS(email={email}) MATCH (u)-[:MEMBER_OF*0..]->()-[:CAN]->(p) RETURN distinct p";
		$query = new Query($this->client, $cypher, array('email' => $id));
		$results = $query->getResultSet();

		$perms = array();
		foreach ($results as $result) {
			$perms[] = $result['p']->getProperties();
		}
		return $perms;
	}

	/**
	 * List all users
	 *
	 * @return array
	 */
	public function getUsers()
	{
		$cypher = 'START u=node:USERS("email:*") RETURN u';
		$query = new Query($this->client, $cypher);
		$results = $query->getResultSet();

		$users = array();
		foreach ($results as $result) {
			$users[] = $result['u']->getProperties();
		}
		return $users;
	}
}