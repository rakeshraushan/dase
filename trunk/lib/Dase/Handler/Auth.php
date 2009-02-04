<?php

class Dase_Handler_Auth extends Dase_Handler
{
	public $resource_map = array(
		'{serviceuser}/{eid}' => 'eidauth',
	);

	protected function setup($r)
	{
		$service_users = Dase_Config::get('serviceuser');
		if (isset($service_users[$r->get('serviceuser')])) {
			//just authorize them
			$service_user = $r->getUser('http');
		} else {
			$r->renderError(401);
		}
	}

	//allows a service user to get htpasswd of a user
	public function getEidauth($r)
	{
		$user = Dase_DBO_DaseUser::get($r->get('eid'));
		if ($user) {
			$r->renderResponse($user->getHttpPassword());
		} else {
			$r->renderError(404);
		}
	}

}

