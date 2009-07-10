<?php

class Dase_Search 
{
	private $bound_params;
	private $search_result = null;
	private $request;
	private $search_array;		
	private $sql;
	private $url;
	private $solr;

	public function __construct($request,$db,$config)
	{
		$this->cache = $request->getCache();
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;
	}

	public function getResult()
	{
		if ($this->search_result) {
			return $this->search_result;
		}
		//sets search_array:
		$this->_parseRequest();

		return $this->_executeSearch();
	}

	private function _parseRequest()
	{
		$request = $this->request;
		$search['att'] = array();
		$search['colls'] = array();
		$search['find'] = array();
		$search['omit'] = array();
		$search['qualified'] = array();
		$search['type'] = null;

		// search syntax:
		// query name is 'q'
		// include phrases in quotes (' or ")
		// use '-' to omit a word or phrase
		// for attribute searches, name is '<coll_ascii_id>~<attribute_ascii_id>'
		// use tilde (as in example) for auto substring/phrase search
		// use single period to match exact value_text string (case-insensitive)
		// add more attribute searches (refinements) by adding them to
		// the query string. Note that the use of '.' or '~' in
		// a query parameter name that is NOT part of the search will
		// make the search fail (since it'll be interpreted as an
		// attribute search
		//
		// also...prepend query term with att_ascii_id to limit
		// to that attribute ascii (allows cross-coll searches!)
		// that's the 'qualified' search 
		//
		// NOTE: everything is assumed to be "and."
		// The word 'and' has *no* boolean significance. 
		// Parentheses have not functional significance.
		//
		/*
		 * exact match:
		 * test.title=farewell+to+arms 
		 *
		 * match substring:
		 * test~title=farewell+to+a
		 *
		 * match item_type:
		 * type=test:picture 
		 *
		 * qualified search:
		 * q=title:farewell+to+arms
		 */

		$this->solr = new Dase_Search_Solr($config); 
		$this->solr->prepareSearch($request); 
	}

	private function _executeSearch()
	{

		return $this->solr->getResultsAsAtom();
		$collection_lookup = Dase_DBO_Collection::getLookupArray($this->db);
		$st = Dase_DBO::query($this->db,$this->sql,$this->bound_params);
		$tallies = array();
		$item_ids = array();
		$items = array();
		//create hit tally per collection:
		while ($row = $st->fetch()) {
			$items[$row['collection_id']][] = $row['id'];
		}
		uasort($items, array('Dase_Util','sortByCount'));
		foreach ($items as $coll_id => $set) {
			$ascii_id = $collection_lookup[$coll_id]['ascii_id'];
			$tallies[$ascii_id]['total'] = count($set);
			$tallies[$ascii_id]['name'] = $collection_lookup[$coll_id]['collection_name'];
			$item_ids = array_merge($item_ids,$set);
		}

		//so failed searches of one collection go to collection page
		if (0 == count($items) && 1 == count($this->search_array['colls'])) {
			$col = $this->search_array['colls'][0];
			foreach ($collection_lookup as $id => $info_array) {
				if ($col == $info_array['ascii_id']) {
					$tallies[$col]['total'] = 0;
					$tallies[$col]['name'] = $info_array['collection_name'];
				}
			}
		}

		//look into caching item_ids here to benefit first sort request?
		//note: sorting all happens here!!! (kind of aspect-ily)
		if ($this->request->has('sort')) {
			$sort = $this->request->get('sort');
			$item_ids = Dase_DBO_Item::sortIdArray($this->db,$sort,$item_ids);
		} else {
			if (1 == count($tallies)) { //we only sort single collection results
				$item_ids = Dase_DBO_Item::sortIdArrayByUpdated($this->db,$item_ids);
			}
		}
		return new Dase_Search_Result($this->db,$item_ids,$tallies,$this->url,$this->search_array);
	}
}

