<?php
interface Dase_CollectionInterface 
{
	//NOTE: these are all now availble as web services 
	static function get($ascii_id);
	function getXml();
	function getItemsXmlByAttVal($att_ascii_id,$value_text,$substr = false);
	function getItemsXmlByType($type_ascii_id);
	/*
	function getXml($limit = 100000);
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
	function buildSearchIndex();
	function createNewItem($serial_number = null);
	function getLastUpdated();
	function getAtom();
	 */
}
