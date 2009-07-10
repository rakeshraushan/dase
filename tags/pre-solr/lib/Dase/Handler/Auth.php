<?php

class Dase_Handler_Auth extends Dase_Handler
{
	public $resource_map = array(
		'{serviceuser}/{eid}' => 'eidauth',
	);

	protected function setup($r)
	{
		$service_users = $r->retrieve('config')->getAuth('serviceuser');
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
		$user = Dase_DBO_DaseUser::get($this->db,$r->get('eid'));
		if ($user) {
			$r->renderResponse($user->getHttpPassword($r->retrieve('config')->getAuth('token')));
		} else {
			$r->renderError(404);
		}
	}

}

