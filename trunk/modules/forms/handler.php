<?php

class Dase_ModuleHandler_Forms extends Dase_Handler {

	public $resource_map = array(
		'/' => 'form',
		'index' => 'form',
		'data' => 'data',
		'data/{serial_number}' => 'data',
	);

	public function setup($r)
	{
		$this->user = $r->getUser();
		$this->fields = Dase_Config::get('fields');
		$this->module = Dase_Config::get('module');
		$this->collection = Dase_DBO_Collection::get(Dase_Config::get('collection_ascii_id'));
		//needed for post privileges
		$this->superuser = Dase_DBO_DaseUser::get('pkeane');
	}

	public function deleteData($r) 
	{
		$item = Dase_DBO_Item::get($this->collection->ascii_id,$r->get('serial_number'));
		if (!$this->user->can('write',$item)) {
			$r->renderError(401,'no go unauthorized');
		}
		$item->expunge();
		$r->renderOk();
	}

	public function getData($r) 
	{
		if (!$this->user->can('read',$this->collection)) {
			$r->renderError(401,'no go unauthorized');
		}
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',Utlookup::getRecord($this->user->eid));
		$tpl->assign('collection',$this->collection);
		$cb = time();
		$tpl->assign('feed',Dase_Atom_Feed::retrieve(APP_ROOT.'/search.atom?c='.$this->collection->ascii_id.'&q=%&tstamp='.$cb));
		$r->renderResponse($tpl->fetch('data.tpl'));

	}

	public function postToData($r)
	{
		//receive form post and create atom entry w/ data
		$entry = new Dase_Atom_Entry;
		$entry->setTitle($this->module);
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
		$r->renderRedirect('modules/'.$this->module);
	}

	public function getForm($r) 
	{
		$tpl = new Dase_Template($r,true);
		$tpl->assign('user',Utlookup::getRecord($this->user->eid));
		$tpl->assign('collection',$this->collection);
		if ($this->user->can('read',$this->collection)) {
			$tpl->assign('admin_user',1);
		}
		$cb = time();
		$tpl->assign('feed',Dase_Atom_Feed::retrieve(APP_ROOT.'/search.atom?'.$this->collection->ascii_id.'.submitter_eid='.$this->user->eid.'&tstamp='.$cb));
		$r->renderResponse($tpl->fetch('index.tpl'));
	}
}
