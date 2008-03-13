<?php


require_once 'cli_setup.php';

class Dase_Remote 
{
	protected $ctx;
	protected $url;

	public function __construct($url,$user='',$pass='',$method='GET') {
		$this->url = trim($url,"/");
		$header = '';
		if ($user && $pass) {
			$auth = base64_encode("$user:$pass");
			$header = "Authorization: Basic $auth";
		} 
		$opts = array(
			'http' => array (
				'method'=>$method,
				'header'=>$header
			));
		$this->ctx = stream_context_create($opts);
	}

	public function get() {
		return file_get_contents($this->url,false,$this->ctx);
	}

}
$url = "http://www.laits.utexas.edu/dasedemo/api/v1";
$remote = new Dase_Remote($url,'dase','api');
$xml = $remote->getAdminAttributes();
Dase_DB_Collection::insertAttributes(0,$xml);
$xml = new SimpleXMLElement($remote->getAll());
foreach ($xml->collection as $collection) {
	$ascii_id = $collection['ascii_id'];
	if (1 || 'art_history_collection' == $ascii_id) {
		print "working on " . $collection['collection_name'] . "\n";
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
	} else {
		print "$ascii_id skipped\n";
	}
}
