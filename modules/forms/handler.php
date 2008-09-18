<?php

class Dase_ModuleHandler_Forms extends Dase_Handler {

	public $resource_map = array(
		'/' => 'new_form',
		'index' => 'new_form',
		'trainee' => 'trainee',
	);

	public function setup($r)
	{
		$this->user = $r->getUser();
	}

	public function postToTrainee($r)
	{
		$fields = array(
			'submitter_name',
			'submitter_eid',
			'submitter_dept',
			'first_name',
			'last_name',
			'email',
			'eid',
			'logon_id',
			'eoffice',
			'edesk',
		);
		$entry = new Dase_Atom_Entry;
		$entry->setTitle('hrms');
		$entry->setUpdated(date(DATE_ATOM));
		$entry->addAuthor($r->getUser()->eid);
		foreach ($fields as $f) {
			$d = Dase_Atom::$ns['d'];
			$entry->addElement('d:'.$f,$r->get($f),$d);
		}
		header("Content-Type: application/atom+xml;type=entry;charset='utf-8'");
		echo $entry->asXml();
		exit;
	}

	public function getNewForm($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',Utlookup::getRecord($this->user->eid));
		$r->renderResponse($tpl->fetch('index.tpl'));
	}
}
