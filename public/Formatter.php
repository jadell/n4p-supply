<?php
/**
 * Format objects for returning as JSON
 */
class Formatter
{
	protected $app;

	/**
	 * @param Silex\Application
	 */
	public function __construct(Silex\Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Format group array
	 *
	 * @param array $group
	 * @return array
	 */
	public function formatGroup($group)
	{
		$group['uri'] = $this->app['baseUrl'].'/group/'.rawurlencode($group['name']);
		$group['members'] = $group['uri'].'/members';
		return $group;
	}

	/**
	 * Format user array
	 *
	 * @param array $user
	 * @return array
	 */
	public function formatUser($user)
	{
		$user['uri'] = $this->app['baseUrl'].'/user/'.rawurlencode($user['email']);
		$user['groups'] = $user['uri'].'/groups';
		return $user;
	}
}