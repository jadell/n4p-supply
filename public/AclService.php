<?php
use Everyman\Neo4j\Client,
    Everyman\Neo4j\Relationship,
    Everyman\Neo4j\Node,
    Everyman\Neo4j\Index\NodeIndex;

class AclService
{
	protected $client;
	protected $userIndex;
	protected $userRef;

	/**
	 *
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Get a single user
	 *
	 * @param string id
	 * @return array
	 */
	public function getUser($id)
	{
		$user = $this->getUserIndex()->findOne('email', $id);
		return $user ? $user->getProperties() : null;
	}

	/**
	 * List all users
	 *
	 * @return array
	 */
	public function getUsers()
	{
		$users = array();
		$userRels = $this->getUserRef()->getRelationships('USER', Relationship::DirectionOut);
		foreach ($userRels as $userRel) {
			$user = $userRel->getEndNode();
			$users[$user->getProperty('email')] = $user->getProperties();
		}

		return $users;
	}

	////////////////////////////////////////////////////////////////////////////////
	// PROTECTED //////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////

	/**
	 * User lookup index
	 *
	 * @return NodeIndex
	 */
	protected function getUserIndex()
	{
		if (!$this->userIndex) {
			$this->userIndex = new NodeIndex($this->client, 'USERS');
		}
		return $this->userIndex;
	}

	/**
	 * User subreference node
	 *
	 * @return Node
	 */
	protected function getUserRef()
	{
		if (!$this->userRef) {
			$ref = $this->client->getReferenceNode();
			$userRefRels = $ref->getRelationships('USERS', Relationship::DirectionOut);
			$this->userRef = $userRefRels[0]->getEndNode();
		}
		return $this->userRef;
	}
}