<?php
Class Dase_DocStore_Solr extends Dase_DocStore
{
	private $solr_base_url;
	private $solr_update_url;
	private $solr_version;


	function __construct($db,$config) 
	{
		$this->solr_base_url = $config->getSearch('solr_base_url');
		$this->solr_update_url = $this->solr_base_url.'/update';;
		$this->solr_version = $config->getSearch('solr_version');
		$this->db = $db;
	}

	public function storeItem($item,$freshness=0)
	{
		//use search engine class
		$engine = new Dase_SearchEngine_Solr($this->db,$this->config);
		return $engine->buildItemIndex($item,$freshness);
	}

	public function deleteItem($item)
	{
		$delete_doc = '<delete><id>'.$item->getUnique().'</id></delete>';
		$resp = Dase_Http::post($this->solr_update_url,$delete_doc,null,null,'text/xml');
		Dase_Http::post($this->solr_update_url,'<commit/>',null,null,'text/xml');
		return $resp;
	}

	public function deleteCollection($coll)
	{
		$start = Dase_Util::getTime();
		$delete_doc = '<delete><query>c:'.$coll.'</query></delete>';
		$resp = Dase_Http::post($this->solr_update_url,$delete_doc,null,null,'text/xml');
		Dase_Http::post($this->solr_update_url,'<commit/>',null,null,'text/xml');
		$end = Dase_Util::getTime();
		$index_elapsed = round($end - $start,4);
		return $resp.' deleted '.$coll.' index: '.$index_elapsed;
	}

	public function commit()
	{
		return Dase_Http::post($this->solr_update_url,'<commit/>',null,null,'text/xml');
	}

	public function getTimestamp($item_unique)
	{
		$url = $this->solr_base_url."/select/?q=_id:".$item_unique."&version=".$this->solr_version;
		Dase_Log::debug(LOG_FILE,'SOLR ITEM RETRIEVE: '.$url);
		$res = file_get_contents($url);
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXml($res);
		foreach ($dom->getElementsByTagName('date') as $el) {
			if ('timestamp' == $el->getAttribute('name')) {
				return $el->nodeValue;
			}
		}
	}

	public function getItem($item_unique,$app_root,$as_feed = false)
	{
		$entry = '';
		$url = $this->solr_base_url."/select/?q=_id:".$item_unique."&version=".$this->solr_version;
		Dase_Log::debug(LOG_FILE,'SOLR ITEM RETRIEVE: '.$url);
		$res = file_get_contents($url);

		//see raw solr response
		//header('Content-type: application/xml');
		//print $res; exit;

		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXml($res);
		foreach ($dom->getElementsByTagName('arr') as $el) {
			if ('atom' == $el->getAttribute('name')) {
				foreach ($el->getElementsByTagName('str') as $at_el) {
					$entry = Dase_Util::unhtmlspecialchars($at_el->nodeValue);
				}
			}
		}
		$entry = str_replace('{APP_ROOT}',$app_root,$entry);
		$added = <<<EOD
<d:extension>here it is</d:extension>
EOD;
		$entry = str_replace('<author>',$added."\n  <author>",$entry);

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


