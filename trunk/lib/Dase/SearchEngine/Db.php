<?php
Class Dase_SearchEngine_Db extends Dase_SearchEngine
{
	function __construct($db,$config) 
	{
		$this->request = $request;
	}

	public function buildItemIndex($item,$freshness)
	{
	}	

	public function buildItemSetIndex($item_array)
	{
	}	

	public function deleteItemIndex($item,$freshness)
	{
	}	

	public function prepareSearch($request,$start=0,$max=30)
	{
	}

	public function getIndexedTimestamp($item)
	{
	}

	public function getResultsAsAtom() 
	{
	}

	public function getResultsAsItemAtom() 
	{
	}

}


