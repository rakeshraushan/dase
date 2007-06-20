<?php
define("DASE_PATH",'../');
ini_set('include_path',ini_get('include_path').':../lib:/dase/lib:'); 
require_once 'Dase/Timer.php';
require_once 'Dase/DB/Collection.php';
require_once 'Dase/Remote.php';

Dase_Timer::start();

$url = "http://dase.laits.utexas.edu/api/v1";
$remote = new Dase_Remote($url,'dase','api');
$xml = $remote->getAdminAttributes();
Dase_DB_Collection::insertAttributes(0,$xml);
$xml = new SimpleXMLElement($remote->getAll());
foreach ($xml->collection as $collection) {
	$ascii_id = $collection['ascii_id'];
	$coll_xml = new SimpleXMLElement($remote->getCollectionInfo($ascii_id));
	Dase_DB_Collection::insertCollection($coll_xml->collection->asXML());
	try {
		print Dase_DB_Collection::insertAttributes($ascii_id,$remote->getAttributes($ascii_id));
	} catch (Exception $e) {
		print $e ."\n";
	}
	print " attributes inserted\n";
	$string = $remote->getItemSerNums($ascii_id);
	$ser_num_array = explode(',',$string);
	$sn_array = array_slice($ser_num_array,0,400);
	foreach ($sn_array as $sn) {
		$xml = $remote->getItem($sn,$ascii_id);
		print Dase_DB_Collection::insertItem($ascii_id,$xml) . "\n";
	}
}
