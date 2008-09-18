<?php

class Dase_ModuleHandler_Forms extends Dase_Handler {

	public $resource_map = array(
		'/' => 'form',
		'index' => 'form',
		'data' => 'data',
	);

	public function setup($r)
	{
		$this->user = $r->getUser();
		$this->fields = array(
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
		$this->collection = Dase_DBO_Collection::get('hrms_form');
		//needed for post privileges
		$this->superuser = Dase_DBO_DaseUser::get('pkeane');
	}

	public function postToData($r)
	{
		//receive form post and create atom entry w/ data
		$entry = new Dase_Atom_Entry;
		$entry->setTitle('hrms');
		$entry->addAuthor($r->getUser()->eid);
		$entry->setEntryType('item');
		$content = "<dl>";
		foreach ($this->fields as $f) {
			$d = Dase_Atom::$ns['d'];
			$entry->addElement('d:'.$f,$r->get($f),$d);
			$content .= "<dt>$f</dt><dd>".$r->get($f)."</dd>";
		}
		$content .= "</dl>";
		$entry->setContent($content,'html');

		//now post that atom entry to the collection
		$ch = curl_init();
		//curl_setopt($ch, CURLOPT_URL, APP_ROOT.'/collection/'.$this->collection->ascii_id.'?auth=http');
		curl_setopt($ch, CURLOPT_URL, APP_ROOT.'/collection/'.$this->collection->ascii_id);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $entry->asXml());
		curl_setopt($ch, CURLOPT_USERPWD,$this->superuser->eid.':'.$this->superuser->getHttpPassword());
		$str  = array(
			"Content-Type: application/atom+xml;type=entry"
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $str);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		Dase_Log::debug(curl_exec($ch));
		curl_close($ch);  
		$r->renderRedirect('hrms');
	}

	public function getForm($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',Utlookup::getRecord($this->user->eid));
		$cb = time();
		$tpl->assign('feed',Dase_Atom_Feed::retrieve(APP_ROOT.'/search.atom?hrms_form.submitter_eid='.$this->user->eid.'&cache_buster='.$cb));
		$r->renderResponse($tpl->fetch('index.tpl'));
	}
}
