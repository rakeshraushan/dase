<?php

class ManageHandler extends Dase_Handler
{
	public $resource_map = array(
		'phpinfo' => 'phpinfo',
		'colors' => 'colors',
		'manager/email' => 'manager_email',
		'eid/{eid}' => 'ut_person',
		'name/{lastname}' => 'ut_person',
	);

	public function setup($request)
	{
		//all routes here require superuser privileges
		$user = $request->getUser();
		if (!$user->isSuperuser()) {
			$request->renderError(401);
		}
	}

	public function getPhpinfo($request)
	{
		phpinfo();
		exit;
	}

	public function getColors($request) 
	{
		$tpl = new Dase_Template($request);
		$request->renderResponse($tpl->fetch('palette.tpl'));
	}

	public function getManagerEmail($request) 
	{
		$cms = new Dase_DBO_CollectionManager;
		foreach ($cms->find() as $cm) {
			if ('none' != $cm->auth_level) {
				$person = Utlookup::getRecord($cm->dase_user_eid);
				if (isset($person['email'])) {
					$managers[] = $person['name']." <".$person['email'].">"; 
				}
			}
		}
		$request->response_mime_type = 'text/plain';
		$request->renderResponse(join("\n",array_unique($managers)));
	}

	public function getUtPerson($request) 
	{
		if ($request->has('lastname')) {
			$person = Utlookup::lookup($request->get('lastname'),'sn');
		} else {
			$person = Utlookup::getRecord($request->get('eid'));
		}
		$request->response_mime_type = 'text/plain';
		$request->renderResponse(var_export($person,true));
	}
}

