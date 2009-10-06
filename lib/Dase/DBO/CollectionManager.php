<?php

require_once 'Dase/DBO/Autogen/CollectionManager.php';

class Dase_DBO_CollectionManager extends Dase_DBO_Autogen_CollectionManager 
{
	public $name;

	public function getUser()
	{
		$user = new Dase_DBO_DaseUser($this->db);
		$user->eid = $this->dase_user_eid;
		return	$user->findOne();
	}

	public static function get($db,$coll_ascii_id,$eid)
	{
		$cm = new Dase_DBO_CollectionManager($db);
		$cm->collection_ascii_id = $coll_ascii_id;
		$cm->dase_user_eid = $eid;
		if ($cm->findOne()) {
			return $cm;
		} else {
			return false;
		}
	}

	public static function listAsAtom($db,$coll_ascii_id,$eid)
	{
	}

	function asAtom($app_root) 
	{
		$e = new Dase_Atom_Entry();
		return $this->injectAtomEntryData($e,$app_root);

	}

	function injectAtomEntryData(Dase_Atom_Entry $entry,$app_root)
	{
		$entry->setTitle('Collection Manager '.$this->dase_user_eid);
		$entry->setId($app_root.'/collection/'.$this->collection_ascii_id.'/manager/'.$this->dase_user_eid);
		$entry->addCategory('collection_manager','http://daseproject.org/category/entrytype');
		$entry->setUpdated($this->created);
		$entry->addAuthor($this->created_by_eid);
		$entry->addCategory($this->auth_level,'http://daseproject.org/category/auth_level');
		$entry->addCategory($this->dase_user_eid,'http://daseproject.org/category/eid');
		$entry->addCategory($this->collection_ascii_id,'http://daseproject.org/category/collection');
		return $entry;
	}
}
