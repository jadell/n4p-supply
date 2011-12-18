<?php
use Symfony\Component\HttpFoundation\Response;

class JsonResponse extends Response
{
	public function __construct($data, $code=200, $headers=array())
	{
		if ($data !== null) {
			$data = json_encode($data);
		}

		parent::__construct($data, $code, array_merge(array(
			'Content-Type' => 'application/json',
		), $headers));
	}
}

