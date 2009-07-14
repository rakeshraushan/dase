<?php
Class Dase_SearchEngine_Solr extends Dase_SearchEngine
{
	private $coll_filters = array();
	private $max;
	private $solr_base_url;
	private $solr_indexer_url;
	private $solr_version;
	private $start;
	private $request;
	public static $specialchars = array(
			'+','-','&&','||','!','(',')','{','}','[',']','^','"','~','*','?',':','\\'
		); //note the last one is a single backslash!


	function __construct($db,$config) 
	{
		$this->solr_base_url = $config->getSearch('solr_base_url');
		$this->solr_indexer_url = $config->getSearch('solr_indexer_url');
		$this->solr_version = $config->getSearch('solr_version');

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
		$query_string = preg_replace('/(^|\?|&|&amp;)collection=([^&]+)/i','',$query_string);

		//get rid of type limit
		$query_string = preg_replace('/(^|\?|&|&amp;)type=([^&]+)/i','',$query_string);

		$collection_param = '';
		$sort_param = '';
		$filter_query = '';

		//collection= trumps any c=
		$coll_filter = $request->get('collection');
		if ($coll_filter) {
			$filter_query = '&fq=collection:'.urlencode('"'.$coll_filter.'"');
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

		//view solr document itself
		if ($this->request->get('solr')) {
			$this->request->response_mime_type = 'application/xml';
			$this->request->renderResponse($res);
		}

		return $res;
	}

	public function getResultsAsAtom() 
	{
		//probably ought to use XMLReader for speed
		$app_root = $this->request->app_root;
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXml($this->_getSearchResults());
		$facets = array();
		$total = 0;
		foreach ($dom->getElementsByTagName('result') as $el) {
			if ('response' == $el->getAttribute('name')) {
				$total = $el->getAttribute('numFound');
			}
		}
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

		if (1 == count($tallied)) {
			$coll = array_search($cname,$GLOBALS['app_data']['collections']);
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
		foreach ($dom->getElementsByTagName('arr') as $el) {
			if ('atom' == $el->getAttribute('name')) {
				//individual atom entries
				foreach ($el->getElementsByTagName('str') as $at_el) { // there will only be ONE
					$num++;
					$setnum = $num + $this->start;
					$entry = Dase_Util::unhtmlspecialchars($at_el->nodeValue);
					$added = <<<EOD
<category term="$setnum" scheme="http://daseproject.org/category/position"/>
  <link rel="http://daseproject.org/relation/search-item" href="{$item_request_url}&amp;num={$setnum}"/>
EOD;
					$entry = str_replace('<author>',$added."\n  <author>",$entry);
					$feed .= $entry;
				}
			}
		}
		$feed .= "</feed>";
		$feed = str_replace('{APP_ROOT}',$app_root,$feed);
		return $feed;
	}

	public function getResultsAsItemAtom() 
	{
		$app_root = $this->request->app_root;
		$dom = new DOMDocument('1.0','utf-8');

		//print($this->_getSearchResults());
		//exit;

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


		foreach ($dom->getElementsByTagName('arr') as $el) {
			if ('atom' == $el->getAttribute('name')) {
				//individual atom entries
				foreach ($el->getElementsByTagName('str') as $at_el) { // there will only be ONE
					$entry = Dase_Util::unhtmlspecialchars($at_el->nodeValue);
					$added = <<<EOD
  <link rel="up" href="{$search_request_url}"/>
  <category term="$num" scheme="http://daseproject.org/category/position"/>
EOD;
					$entry = str_replace('<author>',$added."\n  <author>",$entry);
					$feed .= $entry;
				}
			}
		}
		$feed .= "</feed>";
		$feed = str_replace('{APP_ROOT}',$app_root,$feed);
		return $feed;
	}

	public function buildItemIndex($item,$freshness)
	{
		return $this->postToSolr($item,$freshness);
	}	

	public function buildItemSetIndex($item_array,$freshness)
	{
	}	

	public function deleteItemIndex($item_unique)
	{
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
		$col_obj = $item->getCollection();
		if (!$item->p_collection_ascii_id) {
			$item->p_collection_ascii_id = $col_obj->ascii_id;
			$item->update();
		}
		$updated = $doc->appendChild($dom->createElement('field'));
		$updated->appendChild($dom->createTextNode($item->updated));
		$updated->setAttribute('name','_updated');
		$c = $doc->appendChild($dom->createElement('field'));
		$c->appendChild($dom->createTextNode($col_obj->ascii_id));
		$c->setAttribute('name','c');
		$coll = $doc->appendChild($dom->createElement('field'));
		$coll->appendChild($dom->createTextNode($col_obj->collection_name));
		$coll->setAttribute('name','collection');
		$it_obj = $item->getItemType();
		$it = $doc->appendChild($dom->createElement('field'));
		$it->appendChild($dom->createTextNode($it_obj->ascii_id));
		$it->setAttribute('name','item_type');
		$it_name = $doc->appendChild($dom->createElement('field'));
		$it_name->appendChild($dom->createTextNode($it_obj->name));
		$it_name->setAttribute('name','item_type_name');
		$search_text = array();
		$admin_search_text = array();
		$contents = $item->getContents();
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
		$att_names = array();
		$metadata_array = array();
		foreach ($item->getRawMetadata() as $meta) {
			$metadata_array[$meta['id']]['metadata'] = $meta;
			if (!isset($metadata_array[$meta['id']]['modifier'])) {
				$metadata_array[$meta['id']]['modifier']=array();
			}
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
			$field = $doc->appendChild($dom->createElement('field'));
			$field->appendChild($dom->createTextNode($meta['value_text']));
			//attribute ascii_ids
			$field->setAttribute('name',$meta['ascii_id']);

			$field = $doc->appendChild($dom->createElement('field'));
			$field->appendChild($dom->createTextNode($meta['value_text']));
			$field->setAttribute('name',$meta['attribute_name']);
		}
		foreach ($metadata_array as $m_id => $set) {
			if (count($set['modifier'])) {
				foreach ($set['modifier'] as $mod) {
					$field = $doc->appendChild($dom->createElement('field'));
					$field->appendChild($dom->createTextNode('('.$set['metadata']['value_text'].') '.$mod['value_text']));
					$field->setAttribute('name',$mod['ascii_id']);
				}
			}
		}

		$field = $doc->appendChild($dom->createElement('field'));
		$field->appendChild($dom->createTextNode(join("\n",$search_text)));
		$field->setAttribute('name','_search_text');

		if (count($admin_search_text)) {
			$field = $doc->appendChild($dom->createElement('field'));
			$field->appendChild($dom->createTextNode(join("\n",$admin_search_text)));
			$field->setAttribute('name','admin');
		}
		$entry = new Dase_Atom_Entry_Item;
		$entry = $item->injectAtomEntryData($entry,'{APP_ROOT}');
		$atom_str = $entry->asXml($entry->root);
		$field = $doc->appendChild($dom->createElement('field'));
		$field->appendChild($dom->createTextNode(htmlspecialchars($atom_str)));
		$field->setAttribute('name','atom');
		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	//todo: make autocommit a config option
	public function postToSolr($item,$freshness,$commit=true)
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

		Dase_Log::debug(LOG_FILE,'post to SOLR: '.$this->solr_indexer_url.' item '.$item->getUnique());


		$solr_doc = $this->getItemSolrDoc($item);

		$start_index = Dase_Util::getTime();
		$get_doc_elapsed = round($start_index - $start_get_doc,4);

		$resp = Dase_Http::post($this->solr_indexer_url,$solr_doc,null,null,'text/xml');
		if ($commit) {
			Dase_Http::post($this->solr_indexer_url,'<commit/>',null,null,'text/xml');
		}

		$end = Dase_Util::getTime();
		$index_elapsed = round($end - $start_index,4);

		return $resp.' check: '.$check_elapsed.' get_doc: '.$get_doc_elapsed.' index: '.$index_elapsed;
	}
}

