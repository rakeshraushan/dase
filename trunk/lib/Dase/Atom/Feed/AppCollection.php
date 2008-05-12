<?php
class Dase_Atom_Feed_AppCollection extends Dase_Atom_Feed
{
	function __construct($dom=null)
	{
		parent::__construct($dom);
	}

	function addEntry()
	{
		$entry = new Dase_Atom_Entry_MemberItem($this->dom);
		$this->_entries[] = $entry;
		return $entry;
	}


}
