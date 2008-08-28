<?php

class Dase_Search 
{
	private $bound_params;
	private $search_result = null;
	private $request;
	private $search_array;		
	private $sql;
	private $url;

	public function __construct($request)
	{
		if ($request->has('original_search')) {
			//orignal search elements are simply folded into this
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
		$cache_url = preg_replace('!^search(/item)?!','search',$this->url);
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
			$db_cache = new Dase_DBO_SearchCache;
			$db_cache->query = $cache_url;
			$db_cache->search_md5 = md5($cache_url);
			//get user but do not force login
			$user = $this->request->getUser('cookie',false);
			if ($user) {
				$db_cache->dase_user_id = $user->id;
			}
			$db_cache->timestamp = date(DATE_ATOM);
			$db_cache->insert();
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
		//unique-ify arrays
		$search['find'] = array_unique($search['find']);
		$search['omit'] = array_unique($search['omit']);

		//for attr substring value searches => att~val
		foreach ($request->urlParams as $k => $val) {
			foreach($val as $v) {
				$coll = null;
				$att = null;
				if (('q' != $k) && ('type' != $k) && strpos($k,'~')){
					list($coll,$att) = explode('~',$k);
					$search['att'][$coll][$att]['value_text_substr'] = array();
					$search['att'][$coll][$att]['value_text_substr'][] = $v;
					$search['att'][$coll][$att]['value_text_substr'] = array_unique($search['att'][$coll][$att]['value_text_substr']);
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
				list($coll,$type) = explode(':',$val);
				$search['type']['coll'] = $coll;
				$search['type']['name'] = $type;
			}
		}

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
		if (count($search_table_sets)) {
			$search_table_sql = "SELECT s.item_id FROM search_table s WHERE " . join(' AND ', $search_table_sets);
		}

		//at this point I have my search_table_sql AND search_table_params

		//compile sql for queries of value table 
		$value_table_search_sets = array();
		foreach ($search['att'] as $coll => $att_arrays) {
			foreach ($att_arrays as $att => $ar) {
				$ar_table_sets = array();
				if ($this->_testArray($ar,'value_text_substr')) {
					//the key needs to be specified to make sure it overwrites 
					//(rather than appends) to the array
					foreach ($ar['value_text_substr'] as $k => $term) {
						$ar['find'][$k] = "lower(v.value_text) LIKE ?";
						$value_table_params[] = "%".strtolower($term)."%";
					}
					$ar_table_sets[] = join(' AND ',$ar['value_text_substr']);
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
		//if not explicitly requested, non-public collections will be omitted
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
		return new Dase_Search_Result($item_ids,$tallies,$this->url,$this->search_array);
	}
}

