<?php
Class Dase_SearchEngine_Solr extends Dase_SearchEngine
{
	private $coll_filters = array();
	private $max;
	private $solr_base_url;
	private $solr_update_url;
	private $solr_version;
	private $start;
	private $request;
	public static $specialchars = array(
		'+','-','&&','||','!','(',')','{','}','[',']','^','"','~','*','?',':','\\'
	); //note the last one is a single backslash!


	function __construct($db,$config) 
	{
		$this->solr_base_url = $config->getSearch('solr_base_url');
		$this->solr_update_url = $this->solr_base_url.'/update';
		$this->solr_version = $config->getSearch('solr_version');
		$this->config = $config;

		// n.b. will NOT use db
	}

	private function _cleanUpUrl($url)
	{
		//omit start param 
		$url = preg_replace('/(\?|&|&amp;)start=[0-9]+/i','',$url);

		//omit format param 
		$url = preg_replace('/(\?|&|&amp;)format=\w+/i','',$url);

		//omit max param 
		$url = preg_replace('/(\?|&|&amp;)max=[0-9]+/i','',$url);

		//omit num param
		$url = preg_replace('/(\?|&|&amp;)num=\w+/i','',$url);

		//omit sort param
		$url = preg_replace('/(\?|&|&amp;)sort=\w+/i','',$url);

		//last param only PHP >= 5.2.3
		//$url = htmlspecialchars($url,ENT_COMPAT,'UTF-8',false);
		//beware double encoding
		$url = htmlspecialchars($url,ENT_COMPAT,'UTF-8');

		return $url;
	}

	public function prepareSearch($request,$start,$max,$num=0)
	{
		$this->request = $request;
		$this->start = $start;
		$this->max = $max;
		$this->num = $num;

		if ($num) {
			$start = $num-1;
		}


		$query_string = $request->query_string;

		$matches = array();

		//remove all collection filters
		$query_string = preg_replace('/(^|\?|&|&amp;)c=([^&]+)/i','',$query_string);
		//$query_string = preg_replace('/(^|\?|&|&amp;)collection=([^&]+)/i','',$query_string);
		$query_string = preg_replace('/(^|\?|&|&amp;)collection_ascii_id=([^&]+)/i','',$query_string);

		//get rid of type limit
		$query_string = preg_replace('/(^|\?|&|&amp;)type=([^&]+)/i','',$query_string);

		$collection_param = '';
		$sort_param = '';
		$filter_query = '';

		//collection= trumps any c=
		$coll_filter = $request->get('collection_ascii_id');
		if ($coll_filter) {
			//$filter_query = '&fq=collection:'.urlencode('"'.$coll_filter.'"');
			$filter_query = '&fq=c:'.urlencode('"'.$coll_filter.'"');
		} else {
			$coll_filters = $request->get('c',true);
			if (count($coll_filters) && $coll_filters[0]) {
				$filter_query = '&fq=(c:'.join('+OR+c:',$coll_filters).')';
				$this->coll_filters = $coll_filters;
			}
		}

		$sort = $request->get('sort');
		if ($sort) {
			$sort_param = '&sort='.$sort.'+asc';
			$query_string = preg_replace('/(\?|&|&amp;)sort=\w+/i','',$query_string);
		} else {
			$sort_param = '&sort=_updated+desc';
		}

		$this->solr_search_url = 
			$this->solr_base_url
			.'/select/?'.$query_string
			.$filter_query
			.'&version='.$this->solr_version
			.'&rows='.$max
			.'&start='.$start
			.'&facet=true'
			.'&facet.field=collection'
			.$collection_param
			.$sort_param;

	}

	private function _getSearchResults() 
	{
		Dase_Log::debug(LOG_FILE,'SOLR SEARCH: '.$this->solr_search_url);
		$res = file_get_contents($this->solr_search_url);
		if (false === $res) {
			throw new Dase_SearchEngine_Exception('no search result returned');
		}

		//view solr document itself
		if ($this->request->get('solr')) {
			$this->request->response_mime_type = 'application/xml';
			$this->request->renderResponse($res);
		}

		return $res;
	}

	public function getResultsAsAtom() 
	{
		$app_root = $this->request->app_root;
		//use XMLReader for speed

		$total = 0;
		$coll_tallies = array();
		$entries = array();

		$reader = new XMLReader();
		if (false === $reader->XML($this->_getSearchResults())) {
			throw new Dase_SearchEngine_Exception('error reading search engine xml');
		}
		while ($reader->read()) {
			//get total number found
			if ($reader->localName == "result" && $reader->nodeType == XMLReader::ELEMENT) {
				$total = $reader->getAttribute('numFound');
			}
			//get entries
			if ($reader->localName == "str" && $reader->nodeType == XMLReader::ELEMENT) {
				if ('_atom' == $reader->getAttribute('name')) {
					$reader->read();
					$entries[] = $reader->value;
				}
			}
			//get collection tallies
			if ($reader->localName == "lst" && $reader->nodeType == XMLReader::ELEMENT) {
				if ('collection' == $reader->getAttribute('name')) {
					while ($reader->read()) {
						if ($reader->localName == "int" && $reader->nodeType == XMLReader::ELEMENT) {
							$tally['collection_name'] = $reader->getAttribute('name');
							$tally['coll'] = array_search($tally['collection_name'],$GLOBALS['app_data']['collections']);
							//advance reader
							$reader->read();
							$tally['count'] = $reader->value;
							if ($tally['count']) {
								$coll_tallies[] = $tally;
							}
							$tally = array();
						} 
					}
				}
			}
		}
		$reader->close();

		$url = $this->_cleanUpUrl($this->request->getUrl());
		$grid_url = $url.'&amp;start='.$this->start.'&amp;max='.$this->max.'&amp;display=grid';
		$list_url = $url.'&amp;start='.$this->start.'&amp;max='.$this->max.'&amp;display=list';

		$id = $app_root.'/search/'.md5($url);
		$updated = date(DATE_ATOM);

		//todo: probably the q param
		preg_match('/(\?|&|&amp;)q=([^&]+)/i', urldecode($this->solr_search_url), $matches);
		$query = htmlspecialchars(urlencode($matches[2]));

		$feed = <<<EOD
<feed xmlns="http://www.w3.org/2005/Atom"
	  xmlns:thr="http://purl.org/syndication/thread/1.0">
  <author>
	<name>DASe (Digital Archive Services)</name>
	<uri>http://daseproject.org</uri>
	<email>admin@daseproject.org</email>
  </author>
  <title>DASe Search Result</title>
  <link rel="alternate" title="Search Result" href="$url" type="text/html"/>
  <link rel="related" title="grid" href="$grid_url" type="text/html"/>
  <link rel="related" title="list" href="$list_url" type="text/html"/>
  <updated>$updated</updated>
  <category term="search" scheme="http://daseproject.org/category/feedtype"/>
  <id>$id</id>
  <totalResults xmlns="http://a9.com/-/spec/opensearch/1.1/">$total</totalResults>
  <startIndex xmlns="http://a9.com/-/spec/opensearch/1.1/">$this->start</startIndex>
  <itemsPerPage xmlns="http://a9.com/-/spec/opensearch/1.1/">$this->max</itemsPerPage>
  <Query xmlns="http://a9.com/-/spec/opensearch/1.1/" role="request" searchTerms="$query"/>
EOD;

		//next link
		$next = $this->start + $this->max;
		if ($next <= $total) {
			$next_url = $url.'&amp;start='.$next.'&amp;max='.$this->max;
			$feed .= "  <link rel=\"next\" href=\"$next_url\"/>";
		}

		//previous link
		$previous = $this->start - $this->max;
		if ($previous > 0) {
			$previous_url = $url.'&amp;start='.$previous.'&amp;max='.$this->max;
			$feed .= "  <link rel=\"previous\" href=\"$previous_url\"/>";
		}

		//collection fq
		//this will allow us to create search filters on page forms 
		foreach ($this->coll_filters as $c) {
			$feed .= "  <category term=\"$c\" scheme=\"http://daseproject.org/category/collection_filter\"/>\n";
		}

		$tallied = array();
		foreach ($coll_tallies as $tally) {
			$count = $tally['count'];
			$cname = $tally['collection_name'];
			$cname_specialchars = htmlspecialchars($cname);
			$coll = $tally['coll'];
			$encoded_query = $query.'&amp;c='.$coll;
			$feed .= "  <link rel=\"http://daseproject.org/relation/single_collection_search\" title=\"$cname_specialchars\" thr:count=\"$count\" href=\"q=$encoded_query\"/>\n";
		}

		if (1 == count($coll_tallies)) {
			$feed .= "  <link rel=\"http://daseproject.org/relation/collection\" title=\"$cname_specialchars\" thr:count=\"$count\" href=\"$app_root/collection/$coll\"/>\n";
			$feed .= "  <link rel=\"http://daseproject.org/relation/collection/attributes\" title=\"$cname_specialchars attributes\" href=\"$app_root/collection/$coll/attributes.json\"/>\n";
		}

		//this prevents a 'search/item' becoming 'search/item/item':
		$item_request_url = str_replace('search/item','search',$this->request->url);
		$item_request_url = str_replace('search','search/item',$item_request_url);

		//omit format param 
		$item_request_url = preg_replace('/(\?|&|&amp;)format=\w+/i','',$item_request_url);

		$item_request_url = htmlspecialchars($item_request_url);

		$num = 0;
		foreach ($entries as $entry_txt) {
			$num++;
			$setnum = $num + $this->start;
			$entry = Dase_Util::unhtmlspecialchars($entry_txt);
			$added = <<<EOD
<category term="$setnum" scheme="http://daseproject.org/category/position"/>
  <link rel="http://daseproject.org/relation/search-item" href="{$item_request_url}&amp;num={$setnum}"/>
EOD;
			$entry = str_replace('<author>',$added."\n  <author>",$entry);
			$feed .= $entry;
		}
		$feed .= "</feed>";
		$feed = str_replace('{APP_ROOT}',$app_root,$feed);
		return $feed;
	}

	public function getResultsAsItemAtom() 
	{
		$app_root = $this->request->app_root;
		$dom = new DOMDocument('1.0','utf-8');

		$dom->loadXml($this->_getSearchResults());
		$url = $this->_cleanUpUrl($this->request->getUrl());

		$total = 0;
		foreach ($dom->getElementsByTagName('result') as $el) {
			if ('response' == $el->getAttribute('name')) {
				$total = $el->getAttribute('numFound');
			}
		}

		$id = $app_root.'/search/'.md5($url);
		$updated = date(DATE_ATOM);

		//todo: probably the q param
		preg_match('/(\?|&|&amp;)q=([^&]+)/i', urldecode($this->solr_search_url), $matches);

		//solr escaped " fix
		$query = stripslashes(htmlspecialchars($matches[2]));

		$feed = <<<EOD
<feed xmlns="http://www.w3.org/2005/Atom"
	  xmlns:thr="http://purl.org/syndication/thread/1.0">
  <author>
	<name>DASe (Digital Archive Services)</name>
	<uri>http://daseproject.org</uri>
	<email>admin@daseproject.org</email>
  </author>
  <title>DASe Search Result</title>
  <link rel="alternate" title="Search Result" href="$url" type="text/html"/>
  <updated>$updated</updated>
  <category term="searchitem" scheme="http://daseproject.org/category/feedtype"/>
  <id>$id</id>
  <totalResults xmlns="http://a9.com/-/spec/opensearch/1.1/">$total</totalResults>
  <startIndex xmlns="http://a9.com/-/spec/opensearch/1.1/">$this->start</startIndex>
  <itemsPerPage xmlns="http://a9.com/-/spec/opensearch/1.1/">$this->max</itemsPerPage>
  <Query xmlns="http://a9.com/-/spec/opensearch/1.1/" role="request" searchTerms="$query"/>
EOD;

		$num = $this->num;

		$previous = 0;
		$next = 0;
		if ($num < $total) {
			$next = $num + 1;
		}
		if ($num > 1) {
			$previous = $num - 1;
		}

		//omit format param 
		$item_request_url = preg_replace('/(\?|&|&amp;)format=\w+/i','',$this->request->url);
		//omit num param 
		$item_request_url = preg_replace('/(\?|&|&amp;)num=\w+/i','',$item_request_url);
		$item_request_url = htmlspecialchars($item_request_url);

		$next_url = $item_request_url.'&amp;num='.$next;
		$feed .= "\n  <link rel=\"next\" href=\"$next_url\"/>";

		$previous_url = $item_request_url.'&amp;num='.$previous;
		$feed .= "\n  <link rel=\"previous\" href=\"$previous_url\"/>";

		//collection fq
		//this will allow us to create search filters on page forms 
		foreach ($this->coll_filters as $c) {
			$feed .= "\n  <category term=\"$c\" scheme=\"http://daseproject.org/category/collection_filter\"/>\n";
		}

		$tallied = array();
		foreach ($dom->getElementsByTagName('lst') as $el) {
			if ('collection' == $el->getAttribute('name')) {
				foreach ($el->getElementsByTagName('int') as $coll) {
					$count = $coll->nodeValue;
					if ($count) {
						$cname = $coll->getAttribute('name');
						$tallied[$cname]=1;
						$cname_specialchars = htmlspecialchars($cname);
						$encoded_query = urlencode($query).'&amp;collection='.urlencode($cname);
						$feed .= "  <link rel=\"http://daseproject.org/relation/single_collection_search\" title=\"$cname_specialchars\" thr:count=\"$count\" href=\"q=$encoded_query\"/>\n";
					}
				}
			}
		}

		if (count($tallied)) {
			$coll = array_search($cname,$GLOBALS['app_data']['collections']);
			$feed .= "  <link rel=\"http://daseproject.org/relation/collection\" title=\"$cname_specialchars\" thr:count=\"$count\" href=\"$app_root/collection/$coll\"/>\n";
			$feed .= "  <link rel=\"http://daseproject.org/relation/collection/attributes\" title=\"$cname_specialchars attributes\" href=\"$app_root/collection/$coll/attributes.json\"/>\n";
		}

		$search_request_url = str_replace('search/item','search',$this->request->url);
		//omit format param 
		$search_request_url = preg_replace('/(\?|&|&amp;)format=\w+/i','',$search_request_url);
		//omit num param 
		$search_request_url = preg_replace('/(\?|&|&amp;)num=\w+/i','',$search_request_url);
		$search_request_url = htmlspecialchars($search_request_url);

		foreach ($dom->getElementsByTagName('date') as $el) {
			if ('timestamp' == $el->getAttribute('name')) {
				$timestamp = $el->nodeValue;
			}
		}

		foreach ($dom->getElementsByTagName('str') as $at_el) {
			if ('_atom' == $at_el->getAttribute('name')) {
				//individual atom entries
				$entry = Dase_Util::unhtmlspecialchars($at_el->nodeValue);
				$added = <<<EOD
  <link rel="up" href="{$search_request_url}"/>
  <category term="$num" scheme="http://daseproject.org/category/position"/>
  <category term="$timestamp" scheme="http://daseproject.org/category/indexed_timestamp"/>
EOD;
				$entry = str_replace('<author>',$added."\n  <author>",$entry);
				$feed .= $entry;
			}
		}
		$feed .= "</feed>";
		$feed = str_replace('{APP_ROOT}',$app_root,$feed);
		return $feed;
	}

	public function buildItemIndex($item,$freshness,$commit=false)
	{
		return $this->postToSolr($item,$freshness,$commit);
	}	

	public function buildItemSetIndex($item_array,$freshness)
	{
		foreach ($item_array as $item) {
			$this->postToSolr($item,$freshness);
		}
		return $this->commit();
	}	

	public function deleteItemIndex($item)
	{
		$start = Dase_Util::getTime();
		$delete_doc = '<delete><id>'.$item->getUnique().'</id></delete>';
		$resp = Dase_Http::post($this->solr_update_url,$delete_doc,null,null,'text/xml');
		Dase_Http::post($this->solr_update_url,'<commit/>',null,null,'text/xml');
		$end = Dase_Util::getTime();
		$index_elapsed = round($end - $start,4);
		return $resp.' deleted '.$item->serial_number.' index: '.$index_elapsed;
	}	

	public function getIndexedTimestamp($item)
	{
		$url = $this->solr_base_url."/select/?q=_id:".$item->getUnique()."&version=".$this->solr_version;
		$res = file_get_contents($url);
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXml($res);
		foreach ($dom->getElementsByTagName('date') as $el) {
			if ('timestamp' == $el->getAttribute('name')) {
				return $el->nodeValue;
			}
		}
	}

	public function getLatestTimestamp($coll) 
	{
		$url = $this->solr_base_url."/select/?q=c:".$coll."&start=0&max=1&sort=timestamp+desc&version=".$this->solr_version;
		Dase_Log::debug(LOG_FILE,'query SOLR: '.$url);
		$res = file_get_contents($url);
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXml($res);
		foreach ($dom->getElementsByTagName('date') as $el) {
			if ('timestamp' == $el->getAttribute('name')) {
				return $el->nodeValue;
			}
		}
	}

	public function getItemSolrDoc($item,$wrap_in_add_tag=true)
	{
		$dom = new DOMDocument();

		if ($wrap_in_add_tag) {
			$root_el = $dom->createElement('add');
			$root = $dom->appendChild($root_el);
			$doc_el = $dom->createElement('doc');
			$doc = $root->appendChild($doc_el);
		} else {
			$root_el = $dom->createElement('doc');
			$root = $dom->appendChild($root_el);
			$doc = $root;
		}
		$id = $doc->appendChild($dom->createElement('field'));
		$id->appendChild($dom->createTextNode($item->p_collection_ascii_id.'/'.$item->serial_number));
		$id->setAttribute('name','_id');

		$updated = $doc->appendChild($dom->createElement('field'));
		$updated->appendChild($dom->createTextNode($item->updated));
		$updated->setAttribute('name','_updated');

		$item_id = $doc->appendChild($dom->createElement('field'));
		$item_id->appendChild($dom->createTextNode($item->id));
		$item_id->setAttribute('name','_item_id');

		$serial_number = $doc->appendChild($dom->createElement('field'));
		$serial_number->appendChild($dom->createTextNode($item->serial_number));
		$serial_number->setAttribute('name','_serial_number');

		$c = $doc->appendChild($dom->createElement('field'));
		$c->appendChild($dom->createTextNode($item->p_collection_ascii_id));
		$c->setAttribute('name','c');

		$coll = $doc->appendChild($dom->createElement('field'));
		$coll->appendChild($dom->createTextNode($item->collection_name));
		$coll->setAttribute('name','collection');

		$it = $doc->appendChild($dom->createElement('field'));
		$it->appendChild($dom->createTextNode($item->item_type_ascii_id));
		$it->setAttribute('name','item_type');

		$it_name = $doc->appendChild($dom->createElement('field'));
		$it_name->appendChild($dom->createTextNode($item->item_type_name));
		$it_name->setAttribute('name','item_type_name');

		$search_text = array();
		$admin_search_text = array();
		$contents = $item->getContents();
		//won't run if !$item->content_length
		if ($contents && $contents->text) {
			$content = $doc->appendChild($dom->createElement('field'));
			$content->appendChild($dom->createTextNode($contents->text));
			$content->setAttribute('name','content');
			$content_type = $doc->appendChild($dom->createElement('field'));
			$content_type->appendChild($dom->createTextNode($contents->type));
			$content_type->setAttribute('name','content_type');
			if ('text' === $contents->type) {
				$search_text[] = $contents->text;
			}
		}

		$search_text[] = $item->id;
		$search_text[] = $item->serial_number;

		$att_names = array();
		$metadata_array = array();
		foreach ($item->getMetadata(true) as $meta) {
			$metadata_array[$meta['id']]['metadata'] = $meta;

			//create "bags" for search text & admin text
			if (0 === strpos($meta['ascii_id'],'admin_')) {
				$admin_search_text[] = $meta['value_text'];
			} else {
				$search_text[] = $meta['value_text'];
			}
			if ($meta['url']) {
				$field = $doc->appendChild($dom->createElement('field'));
				$field->appendChild($dom->createTextNode($meta['url']));
				$field->setAttribute('name','metadata_link_url_'.$meta['id']);
				$field = $doc->appendChild($dom->createElement('field'));
				$field->appendChild($dom->createTextNode($meta['attribute_name']));
				$field->setAttribute('name','metadata_link_attribute_'.$meta['id']);
				$field = $doc->appendChild($dom->createElement('field'));
				$field->appendChild($dom->createTextNode('http://daseproject.org/relation/metadata-link/'.$col_obj->ascii_id.'/'.$meta['ascii_id']));
				$field->setAttribute('name','metadata_link_rel_'.$meta['id']);
				$field = $doc->appendChild($dom->createElement('field'));
				$field->appendChild($dom->createTextNode($meta['value_text']));
				$field->setAttribute('name','metadata_link_title_'.$meta['id']);
				//attribute name lookup array before 'do_not_store' change
				$att_names[$meta['ascii_id']] = $meta['attribute_name'];
				$meta['ascii_id'] = 'do_not_store_'.$meta['ascii_id'];
			}

			if ($meta['modifier']) {
				$search_text[] = $meta['modifier'];
				if ($meta['modifier_type']) {
					$field = $doc->appendChild($dom->createElement('field'));
					$field->appendChild($dom->createTextNode($meta['modifier']));
					$field->setAttribute('name',$meta['modifier_type']);
				}
			}

			$field = $doc->appendChild($dom->createElement('field'));
			$field->appendChild($dom->createTextNode($meta['value_text']));
			//attribute ascii_ids
			$field->setAttribute('name',$meta['ascii_id']);

			$field = $doc->appendChild($dom->createElement('field'));
			$field->appendChild($dom->createTextNode($meta['value_text']));
			$field->setAttribute('name',$meta['attribute_name']);
		}

		$field = $doc->appendChild($dom->createElement('field'));
		$field->appendChild($dom->createTextNode(join(" ",$search_text)));
		$field->setAttribute('name','_search_text');

		if (count($admin_search_text)) {
			$field = $doc->appendChild($dom->createElement('field'));
			$field->appendChild($dom->createTextNode(join("\n",$admin_search_text)));
			$field->setAttribute('name','admin');
		}

		$entry = new Dase_Atom_Entry_Item;
		$entry = $item->injectAtomEntryData($entry,'{APP_ROOT}');

		//atom entry version
		$atom_str = $entry->asXml($entry->root);
		$field = $doc->appendChild($dom->createElement('field'));
		$field->appendChild($dom->createTextNode(htmlspecialchars($atom_str)));
		$field->setAttribute('name','_atom');
		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	public function deleteCollectionIndexes($coll)
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

	//todo: make autocommit a config option
	public function postToSolr($item,$freshness,$commit=false)
	{
		$start_check = Dase_Util::getTime();

		if ($freshness) {
			$indexed = $this->getIndexedTimestamp($item);
			if ($indexed > date(DATE_ATOM,time()-$freshness)) {
				return "fresh! not indexed";
			}
		}

		$start_get_doc = Dase_Util::getTime();
		$check_elapsed = round($start_get_doc - $start_check,4);

		Dase_Log::debug(LOG_FILE,'post to SOLR: '.$this->solr_update_url.' item '.$item->getUnique());


		$solr_doc = $this->getItemSolrDoc($item);

		//return $solr_doc;

		$start_index = Dase_Util::getTime();
		$get_doc_elapsed = round($start_index - $start_get_doc,4);

		$resp = Dase_Http::post($this->solr_update_url,$solr_doc,null,null,'text/xml');
		if ($commit) {
			Dase_Http::post($this->solr_update_url,'<commit/>',null,null,'text/xml');
		}

		$end = Dase_Util::getTime();
		$index_elapsed = round($end - $start_index,4);

		return $resp.' check: '.$check_elapsed.' get_doc: '.$get_doc_elapsed.' index: '.$index_elapsed;
	}
}


