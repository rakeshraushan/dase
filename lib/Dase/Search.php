<?php

class Dase_Search 
{
	private $bound_params;
	private $echo_array;		
	private $search_result = null;
	private $request;
	private $search_array;		
	private $sql;
	private $url;

	public function __construct($request)
	{
		if ($request->has('original_search')) {
			$orig = preg_replace('/&amp;/','&',urldecode($request->get('original_search')));
			$orig = preg_replace('/original_search=/','',$orig);
			foreach (explode('&',$orig) as $pair) {
				list($k,$v) = explode('=',$pair);
				//make sure it won't be a repeat
				if (!$request->has($k) || !in_array($v,$request->get($k,true))) {
					$request->setUrlParam($k,$v);
				}
			}
			$request->addQueryString($orig);
		}
		$this->request = $request;
	}

	public function getResult()
	{
		if ($this->search_result) {
			return $this->search_result;
		}
		//omit start & max & format params
		$url = preg_replace('/(\?|&|&amp;)start=[0-9]+/i','',$this->request->getUrl());
		$url = preg_replace('/(\?|&|&amp;)format=\w+/i','',$url);
		$url = preg_replace('/(\?|&|&amp;)num=\w+/i','',$url);
		$url = preg_replace('/(\?|&|&amp;)original_search=[^&]+/i','',$url);
		$this->url = preg_replace('/(\?|&|&amp;)max=[0-9]+/i','',$url);
		Dase_Log::debug('url per search '.$this->url);
		$cache_url = preg_replace('!^search(/item)?!','',$this->url);
		$cache = Dase_Cache::get($cache_url);
		$data = $cache->getData(60*30);
		if ($data) { //30 minutes
			$this->search_result = unserialize($data);
			$this->search_result->url = $this->url;  //so we do not take the cached url
			return $this->search_result;
		} else {

			//sets search_array and echo_array:
			$this->_parseRequest();

			//sets sql and bound_params
			$this->_createSql();

			$this->search_result = $this->_executeSearch();
			$cache->setData(serialize($this->search_result));
			return $this->search_result;
		}
	}

	private function _parseRequest()
	{
		$request = $this->request;
		$search['type'] = null;
		$search['colls'] = array();
		$search['att'] = array();
		$search['find'] = array();
		$search['omit'] = array();
		$search['or'] = array();

		$echo['query'] = '';
		$echo['collection_ascii_id'] = '';
		$echo['sub'] = array();
		$echo['exact'] = array();
		$echo['type'] = '';
		// search syntax:
		// query name is 'q'
		// include phrases in quotes (' or ")
		// use '-' to omit a word or phrase
		// use 'or' for Boolean OR between words or phrases
		// for attribute searches, name is '<coll_ascii_id>~<attribute_ascii_id>'
		// use tilde (as in example) for auto substring/phrase search
		// use single period to match exact value_text string (case-insensitive)
		// add more attribute searches (refinements) by adding them to
		// the query string. Note that the use of '.' or '~' in
		// a query parameter name that is NOT part of the search will
		// make the search fail (since it'll be interpreted as an
		// attribute search
		/*
		 * exact match:
		 * test.title=farewell+to+arms 
		 *
		 * match substring:
		 * test~title=farewell+to+a
		 *
		 * match item_type:
		 * type=test:picture 
		 */

		foreach ($request->get('c',true) as $c) {
			$search['colls'][] = $c;
		}
		$search['colls'] = array_unique($search['colls']);

		//collection_ascii_id trumps (indicated by 'collection' query parameter)
		if ($request->has('collection_ascii_id')) {
			$collection_ascii_id = $request->get('collection_ascii_id');
			$search['colls'] = array($collection_ascii_id);
			$echo['collection_ascii_id'] = $collection_ascii_id;
		}

		//populate general find and omit array
		$query = $request->get('q',true);
		$echo['query'] = join(' AND ',$query);
		foreach ($query as $q) {
			foreach ($this->_tokenizeQuoted($q) as $t) {
				if ('-' == substr($t,0,1)) {
					$search['omit'][] = substr($t,1);
				} else {
					$search['find'][] = $t;
				}
			}
		}
		//for substring att searches  => att~val
		foreach ($request->urlParams as $k => $val) {
			if (('q' != $k) && ('type' != $k) && strpos($k,'~')){
				//$echo['sub'][$k] = join(' ',$val);
				$echo['sub'][$k] = $val;
				$coll = null;
				$att = null;
				$tokens = array();
				list($coll,$att) = explode('~',$k);
				if ($coll && $att) {
					$search['att'][$coll][$att]['find'] = array();
					$search['att'][$coll][$att]['omit'] = array();
					$search['att'][$coll][$att]['or'] = array();
					foreach($val as $v) {
						foreach ($this->_tokenizeQuoted($v) as $t) {
							$tokens[] = $t;
						}
					}
					foreach ($tokens as $tok) {
						if ('-' == substr($tok,0,1)) {
							$search['att'][$coll][$att]['omit'][] = substr($tok,1);
						} else {
							$search['att'][$coll][$att]['find'][] = $tok;
						}
					}
					$or_keys = array_keys($search['att'][$coll][$att]['find'],'or');
					foreach ($or_keys as $or_key) {
						foreach(array($or_key-1,$or_key,$or_key+1) as $k) {
							if (array_key_exists($k,$search['att'][$coll][$att]['find'])) {
								$search['att'][$coll][$att]['or'][] = $search['att'][$coll][$att]['find'][$k];
							}
						}
					}
					//purge the find array of all tokens in the or array
					if (isset($search['att'][$coll][$att]['or'])) {
						foreach ($search['att'][$coll][$att]['or'] as $k => $v) {
							while (false !== array_search($v,$search['att'][$coll][$att]['find'])) {
								$remove_key = array_search($v,$search['att'][$coll][$att]['find']);
								unset($search['att'][$coll][$att]['find'][$remove_key]);
							}
						}
					}
					if (isset($search['att'][$coll][$att]['or'])) {
						while (false !== array_search('or',$search['att'][$coll][$att]['or'])) {
							$remove_key = array_search('or',$search['att'][$coll][$att]['or']);
							unset($search['att'][$coll][$att]['or'][$remove_key]);
						}
					}
					if (isset($search['att'][$coll][$att]['find'])) {
						$search['att'][$coll][$att]['find'] = array_unique($search['att'][$coll][$att]['find']);
					}
					if (isset($search['att'][$coll][$att]['omit'])) {
						$search['att'][$coll][$att]['omit'] = array_unique($search['att'][$coll][$att]['omit']);
					}
					if (isset($search['att'][$coll][$att]['or'])) {
						$search['att'][$coll][$att]['or'] = array_unique($search['att'][$coll][$att]['or']);
					}
				}
			}
		}
		//for attr exact value searches => att.val
		foreach ($request->urlParams as $k => $val) {
			foreach($val as $v) {
				$coll = null;
				$att = null;
				if (strpos($k,'.') && !strpos($k,'~') && !strpos($k,':')){
					list($coll,$att) = explode('.',$k);
					$echo['exact'][$k][] = $v;
					$search['att'][$coll][$att]['value_text'] = array();
					$search['att'][$coll][$att]['value_text'][] = $v;
					$search['att'][$coll][$att]['value_text'] = array_unique($search['att'][$coll][$att]['value_text']);
				}
			}
		}

		//for item_type filter
		foreach ($request->urlParams as $k => $val) {
			if (('type' == $k) && $val) {
				if (is_array($val)) {
					$val = array_pop($val);
				}
				$echo['type'] = $val;
				list($coll,$type) = explode(':',$val);
				$search['type']['coll'] = $coll;
				$search['type']['name'] = $type;
			}
		}

		$or_keys = array_keys($search['find'],'or');
		//configure global 'or' set
		foreach ($or_keys as $or_key) {
			foreach(array($or_key-1,$or_key,$or_key+1) as $k) {
				if (array_key_exists($k,$search['find'])) {
					if (!array_search($search['find'][$k],$search['or'])) {
						$search['or'][] = $search['find'][$k];
					}
				}
			}
			$search['or'] = array_unique($search['or']);
		}
		//unique-ify arrays
		$search['find'] = array_unique($search['find']);
		$search['omit'] = array_unique($search['omit']);

		foreach ($search['or'] as $k => $v) {
			while (false !== array_search($v,$search['find'])) {
				$remove_key = array_search($v,$search['find']);
				unset($search['find'][$remove_key]);
			}
		}
		while (false !== array_search('or',$search['or'])) {
			$remove_key = array_search('or',$search['or']);
			unset($search['or'][$remove_key]);
		}

		$this->echo_array = $echo;
		$this->search_array = $search;

		// DONE parsing search string!!
	}
	private function _tokenizeQuoted($string)
	{
		//from php.net:
		for($tokens=array(), $nextToken=strtok($string, ' '); $nextToken!==false; $nextToken=strtok(' ')) {
			if($nextToken{0}=='"')
				$nextToken = $nextToken{strlen($nextToken)-1}=='"' ?
					substr($nextToken, 1, -1) : substr($nextToken, 1) . ' ' . strtok('"');
			$tokens[] = $nextToken;
		}
		return $tokens;
	}

	private function _testArray($a,$key)
	{
		if ( isset($a[$key]) && is_array($a[$key]) && count($a[$key]) && isset($a[$key][0]) && $a[$key][0]) {
			return true;
		}
		return false;
	}

	private function _createSql()
	{
		$search = $this->search_array;
		$search_table_params = array();
		$value_table_params = array();
		$bound_params = array();

		//compile sql for queries of search_table (i.e. search index)
		$search_table_sets = array();
		if (count($search['find'])) {
			//the key needs to be specified 
			//(it is just the number index) to make sure it overwrites 
			//(rather than appends) to the array
			foreach ($search['find'] as $k => $term) {
				$search['find'][$k] = "lower(s.value_text) LIKE ?";
				$search_table_params[] = "%".strtolower($term)."%";
			}
			$search_table_sets[] = join(' AND ',$search['find']);
		}
		if (count($search['omit'])) {
			foreach ($search['omit'] as $k => $term) {
				$search['omit'][$k] = "lower(s.value_text) NOT LIKE ?";
				$search_table_params[] = "%".strtolower($term)."%";
			}
			$search_table_sets[] = join(' AND ',$search['omit']);
		}
		if (count($search['or'])) {
			foreach ($search['or'] as $k => $term) {
				$search['or'][$k] = "lower(s.value_text) LIKE ?";
				$search_table_params[] = "%".strtolower($term)."%";
			}
			$search_table_sets[] = "(" . join(' OR ',$search['or']) . ")";
		}
		if (count($search_table_sets)) {
			$search_table_sql = "SELECT s.item_id FROM search_table s WHERE " . join(' AND ', $search_table_sets);
		}

		//at this point I have my search_table_sql AND search_table_params

		//compile sql for queries of value table 
		$value_table_search_sets = array();
		foreach ($search['att'] as $coll => $att_arrays) {
			foreach ($att_arrays as $att => $ar) {
				$ar_table_sets = array();
				if ($this->_testArray($ar,'find')) {
					//the key needs to be specified to make sure it overwrites 
					//(rather than appends) to the array
					foreach ($ar['find'] as $k => $term) {
						$ar['find'][$k] = "lower(v.value_text) LIKE ?";
						$value_table_params[] = "%".strtolower($term)."%";
					}
					$ar_table_sets[] = join(' AND ',$ar['find']);
				}
				if ($this->_testArray($ar,'omit')) {
					foreach ($ar['omit'] as $k => $term) {
						$ar['omit'][$k] = "lower(v.value_text) NOT LIKE ?";
						$value_table_params[] = "%".strtolower($term)."%";
					}
					$ar_table_sets[] = join(' AND ',$ar['omit']);
				}
				if ($this->_testArray($ar,'or')) {
					foreach ($ar['or'] as $k => $term) {
						$ar['or'][$k] = "lower(v.value_text) LIKE ?";
						$value_table_params[] = "%".strtolower($term)."%";
					}
					$ar_table_sets[] = "(" . join(' OR ',$ar['or']) . ")";
				}
				if ($this->_testArray($ar,'value_text')) {
					foreach ($ar['value_text'] as $k => $term) {
						//note that exact searches are CASE INSENSITIVE
						$ar['value_text'][$k] = "lower(v.value_text) = ?";
						$value_table_params[] = strtolower($term);
					}
					$ar_table_sets[] = join(' AND ',$ar['value_text']);
				}
				if (count($ar_table_sets)) {
					if (false === strpos($att,'admin_')) {
						$value_table_search_sets[] = 
							"id IN (SELECT v.item_id FROM value v,collection c,attribute a WHERE a.collection_id = c.id AND v.attribute_id = a.id AND ".join(' AND ', $ar_table_sets)." AND c.ascii_id = ? AND a.ascii_id = ?)";
					} else {
						//it's an admin attribute, so collection_id is 0
						$value_table_search_sets[] = 
							"id IN (SELECT v.item_id FROM value v,collection c,attribute a WHERE a.collection_id = 0 AND v.attribute_id = a.id AND ".join(' AND ', $ar_table_sets)." AND c.ascii_id = ? AND a.ascii_id = ?)";
					}
				}
				$value_table_params[] = $coll;
				$value_table_params[] = $att;
			}
			unset($ar_table_sets);
		}
		if (count($search['colls']) && isset($search_table_sql)) {
			foreach ($search['colls'] as $ccc) {
				$placeholders[] = '?'; 
			}
			$ph = join(",",$placeholders);
			unset($placeholders);
			$search_table_params = array_merge($search_table_params,$search['colls']);
			$search_table_sql .= " AND collection_id IN (SELECT id FROM collection WHERE ascii_id IN ($ph))";
		}
		//if not explicitly requested, non-public collecitons will be omitted
		if (!count($search['colls']) && isset($search_table_sql)) {
			//make sure this boolean query is portable!!!
			//$search_table_sql .= " AND collection_id IN (SELECT id FROM collection WHERE is_public = '1')";
		}
		if (isset($search_table_sql) && count($value_table_search_sets)) {
			$sql = 
				"SELECT id, collection_id FROM item WHERE id IN ($search_table_sql) AND " . join(' AND ',$value_table_search_sets);
			$bound_params = array_merge($bound_params,$search_table_params);
			$bound_params = array_merge($bound_params,$value_table_params);
		} elseif (isset($search_table_sql)) {
			$sql = 
				"SELECT id, collection_id FROM item WHERE id IN ($search_table_sql)";
			$bound_params = array_merge($bound_params,$search_table_params);
		} elseif (count($value_table_search_sets)) {
			$sql = 
				"SELECT id, collection_id FROM item WHERE " . join(' AND ',$value_table_search_sets);
			$bound_params = array_merge($bound_params,$value_table_params);
			//if searching ONLY for item type (NOT simply as filter)
			//as indicated by lack of other queries (i.e., we got to this point in decision tree)
		} elseif (isset($search['type']['coll']) && isset($search['type']['name'])) {
			$sql =
				"SELECT id, collection_id FROM item WHERE item_type_id IN (SELECT id FROM item_type WHERE ascii_id = ? AND collection_id IN (SELECT id FROM collection WHERE ascii_id = ?))";
			$bound_params[] = $search['type']['name'];
			$bound_params[] = $search['type']['coll'];
		} else {
			//null 
			$sql = 'SELECT id, collection_id FROM item WHERE 1 = 2';
		}
		//if search type is used as filter:
		if (isset($search['type']['coll']) && isset($search['type']['name']) && 
			(isset($search_table_sql) || count($value_table_search_sets))) {
				$sql .=
					"AND item_type_id IN (SELECT id FROM item_type WHERE ascii_id = ? AND collection_id IN (SELECT id FROM collection WHERE ascii_id = ?))";
				$bound_params[] = $search['type']['name'];
				$bound_params[] = $search['type']['coll'];
			}
		$this->sql = $sql;
		$this->bound_params = $bound_params;
	}

	private function _executeSearch()
	{
		$collection_lookup = Dase_DBO_Collection::getLookupArray();
		$st = Dase_DBO::query($this->sql,$this->bound_params);
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
		return new Dase_Search_Result($item_ids,$tallies,$this->url,$this->echo_array);
	}
}

