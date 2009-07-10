<?php

Class Dase_DocStore_Db extends Dase_DocStore
{

	function __construct($db,$config)
	{
		$this->db = $db;
	}

	public function storeItem($item,$freshness=0)
	{
		if ($freshness) {
			$indexed = $this->getTimestamp($item->getUnique());
			if ($indexed > date(DATE_ATOM,time()-$freshness)) {
				return "fresh! not stored";
			}
		}

		$atom = new Dase_DBO_ItemAsAtom($this->db);
		$atom->relative_url = $item->getUnique();
		if (!$atom->findOne()) {
			$atom->insert();
		}
		$entry = $item->injectAtomEntryData(new Dase_Atom_Entry_Item,'{APP_ROOT}');
		$atom->updated = date(DATE_ATOM);
		$atom->xml = $entry->asXml($entry->root); //so we don't get xml declaration
		$atom->update();
		return $atom->xml;
	}

	public function getTimestamp($item_unique)
	{
		$atom = new Dase_DBO_ItemAsAtom($this->db);
		$atom->relative_url = $item_unique;
		if ($atom->findOne()) {
			return $atom->updated;
		} else {
			throw new Dase_DocStore_Exception('no such item');	
		}
	}

	public function getItem($item_unique,$app_root,$as_feed = false)
	{
		$atom = new Dase_DBO_ItemAsAtom($this->db);
		$atom->relative_url = $item_unique;
		if ($atom->findOne()) {
			$entry = $atom->xml;
		} else {
			throw new Dase_DocStore_Exception('no such item');	
		}
		$entry = str_replace('{APP_ROOT}',$app_root,$entry);
		if ($as_feed) {
			$updated = date(DATE_ATOM);
			$id = 'tag:daseproject.org,'.date("Y-m-d").':'.Dase_Util::getUniqueName();
			$feed = <<<EOD
<feed xmlns="http://www.w3.org/2005/Atom"
	  xmlns:d="http://daseproject.org/ns/1.0">
  <author>
	<name>DASe (Digital Archive Services)</name>
	<uri>http://daseproject.org</uri>
	<email>admin@daseproject.org</email>
  </author>
  <title>DASe Item as Feed</title>
  <updated>$updated</updated>
  <category term="item" scheme="http://daseproject.org/category/feedtype"/>
  <id>$id</id>
  $entry
</feed>
EOD;
			return $feed;
		}
		return $entry;
	}
}


