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
	 * List all groups
	 *
	 * @return array
	 */
	public function getGroups()
	{
		$cypher = "START z=node(0) MATCH (z)-[:GROUPS]->()-[:GROUP]->(g) RETURN g";
		$query = new Query($this->client, $cypher);
		$results = $query->getResultSet();

		$groups = array();
		foreach ($results as $result) {
			$groups[] = $result['g']->getProperties();
		}
		return $groups;
	}

	/**
	 * Get a single group
	 *
	 * @param string $id
	 * @return array
	 */
	public function getGroup($id)
	{
		$cypher = "START z=node(0) MATCH (z)-[:GROUPS]->()-[:GROUP]->(g) WHERE g.name={name} RETURN g";
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
		$cypher = "START z=node(0)".
		          " MATCH (z)-[:GROUPS]->()-[:GROUP]->(g)<-[:MEMBER_OF*1..$depth]-(m)".
		          " WHERE g.name={name} RETURN distinct m";
		$query = new Query($this->client, $cypher, array('name' => $id));
		$results = $query->getResultSet();

		$members = array();
		foreach ($results as $result) {
			$members[] = $result['m']->getProperties();
		}
		return $members;
	}

	/**
	 * Get a single user
	 *
	 * @param string $id
	 * @return array
	 */
	public function getUser($id)
	{
		$cypher = "START z=node(0) MATCH (z)-[:USERS]->()-[:USER]->(u) WHERE u.email={email} RETURN u";
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
		$cypher = "START z=node(0)".
		          " MATCH (z)-[:USERS]->()-[:USER]->(u)-[:MEMBER_OF*1..$depth]->(g)".
		          " WHERE u.email={email} RETURN distinct g";
		$query = new Query($this->client, $cypher, array('email' => $id));
		$results = $query->getResultSet();

		$groups = array();
		foreach ($results as $result) {
			$groups[] = $result['g']->getProperties();
		}
		return $groups;
	}

	/**
	 * List all users
	 *
	 * @return array
	 */
	public function getUsers()
	{
		$cypher = "START z=node(0) MATCH (z)-[:USERS]->()-[:USER]->(u) RETURN u";
		$query = new Query($this->client, $cypher);
		$results = $query->getResultSet();

		$users = array();
		foreach ($results as $result) {
			$users[] = $result['u']->getProperties();
		}
		return $users;
	}
}