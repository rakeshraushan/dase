<?php

class Dase_SearchEngine_Exception extends Exception {
}

class Dase_SearchEngine
{
	private function __construct() {}

	public static function get($db,$config)
	{
		$class_name = 'Dase_SearchEngine_'.ucfirst($config->getSearch('engine'));
		if (class_exists($class_name)) {
			return new $class_name($db,$config);
		} else {
			throw new Dase_SearchEngine_Exception("Error: $class_name is not a valid class!");
		}
	}

	//must be overridden:
	public function buildItemIndex($item,$freshness) {}	
	public function buildItemSetIndex($item_array,$freshness) {}	
	public function getIndexedTimestamp($item) {}
	public function getResultsAsAtom(){} 
	public function getResultsAsJson(){} 
	public function prepareSearch($request,$start=0,$max=30) {}

}


