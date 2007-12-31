<?php
interface Dase_CollectionInterface 
{
	//NOTE: these are all now availble as web services 
	static function get($ascii_id);
	function buildSearchIndex();
}
