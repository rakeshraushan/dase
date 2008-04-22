<?php

require_once 'Dase/DBO/Autogen/CollectionManager.php';

class Dase_DBO_CollectionManager extends Dase_DBO_Autogen_CollectionManager 
{
	public $name;

	public function getUser()
	{
		$user = new Dase_DBO_DaseUser;
		$user->eid = $this->dase_user_eid;
		return	$user->findOne();
	}

	function injectAtomEntryData(Dase_Atom_Entry $entry)
	{
		$entry->setTitle('Collection Manager '.$this->dase_user_eid);
		$entry->setId(APP_ROOT.'/collection_manager/'.$this->collection_ascii_id.'/'.$this->dase_user_eid);
		$entry->addCategory('collection/manager','http://daseproject.org/category/collection','collection manager');
		if (is_numeric($this->updated)) {
			$updated = date(DATE_ATOM,$this->updated);
		} else {
			$updated = $this->updated;
		}
		$entry->setUpdated($updated);
		$entry->addAuthor('ss');
		$div = simplexml_import_dom($entry->setContent());
		$ul = $div->addChild('ul');
		$ul->addAttribute('class','xoxo');
		$coll = $ul->addChild('li',$this->collection_ascii_id);
		$coll->addAttribute('class','collection_ascii_id');
		$user = $ul->addChild('li',$this->dase_user_eid);
		$user->addAttribute('class','eid');
		$auth = $ul->addChild('li',$this->auth_level);
		$auth->addAttribute('class','auth_level');
		return $entry;
	}
}
