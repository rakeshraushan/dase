<?php

class Solr {

	private $url;
	private $q;
	private $rows;
	private $start;
	private $app_root;

	public function __construct($r) 
	{
		$this->q = $r->get('q');
		$this->url = $r->retrieve('config')->get('solr_url');
		$this->rows = $r->get('rows');
		$this->app_root = $r->app_root;
		$this->start = $r->get('start');
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
		$res = file_get_contents($this->url."/select/?q=".$this->q."&version=2.2".$rows_param.$start_param);
		return $res;
	}

	public function getResultsAsAtom() 
	{
		$feed = '<feed xmlns="http://www.w3.org/2005/Atom">
			<author>
			<name>DASe (Digital Archive Services)</name>
			<uri>http://daseproject.org</uri>
			<email>admin@daseproject.org</email>
			</author>
			<title>DASe Search Result</title>
			<link rel="alternate" title="Search Result" href="search?american_west.artist=Adam%20Clark%20Vroman" type="text/html"/>
			<updated>2009-06-02T09:08:39-05:00</updated>
			<category term="search" scheme="http://daseproject.org/category/feedtype"/>
			<id>http://quickdraw.laits.utexas.edu/dase1/search/b3816f8d839b5a10ce8ec8854e1eddd3</id>
			<totalResults xmlns="http://a9.com/-/spec/opensearch/1.1/">10</totalResults>
			<startIndex xmlns="http://a9.com/-/spec/opensearch/1.1/">1</startIndex>
			<itemsPerPage xmlns="http://a9.com/-/spec/opensearch/1.1/">30</itemsPerPage>
			<Query xmlns="http://a9.com/-/spec/opensearch/1.1/" role="request" searchTerms="&amp;quot;artist:Adam Clark Vroman&amp;quot;"/>
			';
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

