<?php
use Everyman\Neo4j\Client;

class AclService
{
	protected $client;

	protected $users = array(
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
		return isset($this->users[$id]) ? $this->users[$id] : null;
	}

	/**
	 * List all users
	 *
	 * @return array
	 */
	public function getUsers()
	{
		return $this->users;
	}
}