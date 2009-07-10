<?php

Class Dase_DocStore_File extends Dase_DocStore
{

	function __construct($db,$config) 
	{
		$this->db = $db;
		$this->doc_root = $config->getMediaDir();
	}

	public function storeItem($item,$freshness=0)
	{
		if ($freshness) {
			$indexed = $this->getTimestamp($item->getUnique());
			if ($indexed > date(DATE_ATOM,time()-$freshness)) {
				return "fresh! not stored";
			}
		}

		$entry = $item->injectAtomEntryData(new Dase_Atom_Entry_Item,'{APP_ROOT}');
		$item_xml = $entry->asXml($entry->root); //so we don't get xml declaration

		$filepath = $this->_getFilepath($item->getUnique());

		//returns no of bytes on success, false if unsuccessful
		return file_put_contents($filepath,$item_xml);
	}


	private function _getFilepath($item_unique)
	{
		list($coll_ascii,$sernum) = explode('/',$item_unique);

		if (!$coll_ascii || !$sernum) {
			throw new Dase_DocStore_Exception($item_unique.' is not a valid item unique');
		}

		if (is_link($this->doc_root.'/'.$coll_ascii)) {
			$directory = $this->doc_root.'/'.$coll_ascii.'_collection/atom';
			if (!file_exists($directory)) {
				throw new Dase_DocStore_Exception($directory.' does not exist');
			}
		} else {
			$directory = $this->doc_root.'/'.$coll_ascii.'/atom';
		}


		$subdir = substr(md5($sernum),0,2);

		if (!file_exists($directory.'/'.$subdir)) {
			mkdir($directory.'/'.$subdir);
			chmod($directory.'/'.$subdir,0770);
		}

		return $directory.'/'.$subdir.'/'.$sernum.'.atom';
	}

	public function getTimestamp($item_unique)
	{
		$filepath = $this->_getFilepath($item_unique);
		if (!file_exists($filepath)) {
			return 0;
		}
		$stat = stat($filepath);
		return $stat['mtime'];
	}

	public function getItem($item_unique,$app_root,$as_feed = false)
	{
		//work on this
	}
}


