<?php

class Dase_Handler_Auth extends Dase_Handler
{
	public $resource_map = array(
		'{serviceuser}/{eid}' => 'eidauth',
	);

	protected function setup($r)
	{
		$serviceusers = $r->getServiceusers();
		if (isset($serviceusers[$r->get('serviceuser')])) {
			//just authorize them
			$serviceuser = $r->getUser('http');
		} else {
			$r->renderError(401);
		}
	}

	//allows a service user to get htpasswd of a user
	public function getEidauth($r)
	{
		$user = Dase_DBO_DaseUser::get($this->db,$r->get('eid'));
		if ($user) {
			$r->renderResponse($user->getHttpPassword($r->getAuthToken()));
		} else {
			$r->renderError(404);
		}
	}

}

