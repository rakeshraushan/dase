<?php
interface Dase_CollectionInterface 
{
	//NOTE: these are all now availble as web services 
	static function get($ascii_id);
	function buildSearchIndex();
	/*
	function getItemsByAttVal($att_ascii_id,$value_text,$substr = false);
	function getItemsByType($type_ascii_id);
	function get($ascii_id);
	function getAttributes($sort = null);
	function getAdminAttributes();
	function getAdminAttributeAsciiIds();
	function getItemCount();
	function getItems();
	function getItemTypes();
	function insertCollection($xml);
	function insertAttributes($ascii_id,$xml);
	function insertItem($ascii_id,$xml);
	function createNewItem($serial_number = null);
	function getLastUpdated();
	function getAtom();
	 */
}
