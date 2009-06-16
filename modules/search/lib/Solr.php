<?php

class Solr {

	private $url;
	private $q;
	private $rows;
	private $solr_url;
	private $start;
	private $app_root;

	public function __construct($r) 
	{
		$this->q = $r->get('q');
		$this->solr_url = $r->retrieve('config')->get('solr_url');
		$this->rows = $r->get('rows');
		$this->app_root = $r->app_root;
		$this->start = $r->get('start');

		//omit start & max & format params
		$url = preg_replace('/(\?|&|&amp;)start=[0-9]+/i','',$r->getUrl());
		$url = preg_replace('/(\?|&|&amp;)format=\w+/i','',$url);
		$url = preg_replace('/(\?|&|&amp;)num=\w+/i','',$url);
		$this->url = preg_replace('/(\?|&|&amp;)max=[0-9]+/i','',$url);
		//$cache_url = preg_replace('!^search(/item)?!','search',$this->url);
		//$data = $this->cache->getData($cache_url,60*30);
	}

	public function getResults() 
	{
		if ($this->rows) {
			$rows_param = '&rows='.$this->rows;
		} else {
			$rows_param = '&rows=10';
		}
		if ($this->start) {
			$start_param = '&start='.$this->start;
		} else {
			$start_param = '&start=0';
		}
		//print $this->url."/select/?q=".$this->q."&version=2.2&start=0".$rows_param."&indent=on";
		$res = file_get_contents($this->solr_url."/select/?q=".$this->q."&version=2.2".$rows_param.$start_param);
		return $res;
	}

	public function getResultsAsAtom() 
	{
		$id = $this->app_root.'/search/'.md5($this->url);
		$updated = date(DATE_ATOM);
		$feed = <<<EOD
		<feed xmlns="http://www.w3.org/2005/Atom">
			<author>
			<name>DASe (Digital Archive Services)</name>
			<uri>http://daseproject.org</uri>
			<email>admin@daseproject.org</email>
			</author>
			<title>DASe Search Result</title>
			<link rel="alternate" title="Search Result" href="$this->url" type="text/html"/>
			<updated>$updated</updated>
			<category term="search" scheme="http://daseproject.org/category/feedtype"/>
			<id>$id</id>
			<totalResults xmlns="http://a9.com/-/spec/opensearch/1.1/"></totalResults>
			<startIndex xmlns="http://a9.com/-/spec/opensearch/1.1/">$this->start</startIndex>
			<itemsPerPage xmlns="http://a9.com/-/spec/opensearch/1.1/">$this->rows</itemsPerPage>
			<Query xmlns="http://a9.com/-/spec/opensearch/1.1/" role="request" searchTerms="&amp;quot;artist:Adam Clark Vroman&amp;quot;"/>
EOD;
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXml($this->getResults());
		foreach ($dom->getElementsByTagName('arr') as $el) {
			if ('atom' == $el->getAttribute('name')) {
				foreach ($el->getElementsByTagName('str') as $at_el) {
					$feed .= unhtmlspecialchars($at_el->nodeValue);
				}
			}
		}
		$feed .= "</feed>";
		$feed = str_replace('{APP_ROOT}',$this->app_root,$feed);
		return $feed;
	}
}

