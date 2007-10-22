<? 
class Dase_Search {

	public $search;		
	public $echo;		

	function __construct() {
		$url_params = Dase::instance()->url_params;
		$search['type'] = null;
		$search['coll_ids'] = array();
		$search['att'] = array();
		$search['find'] = array();
		$search['omit'] = array();
		$search['or'] = array();

		// search syntax:
		// query name is 'q'
		// include phrases in quotes (' or ")
		// use '-' to omit a word or phrase
		// use 'or' for Boolean OR between words or phrases
		// for attribute searches, name is '<coll_ascii_id>.<attribute_ascii_id>'
		// use period (as in example) for auto substring/phrase search
		// use single colon to match md5 hash of exact value_text string
		// add more attribute searches (refinements) by adding them to
		// the query string. Note that the use of '.' or ':' in
		// a query parameter name that is NOT part of the search will
		// make the search fail (since it'll be interpreted as an
		// attribute search

		if (isset($url_params['cid'])) {
			if (!is_array($url_params['cid'])) {
				$url_params['cid'] = array($url_params['cid']);
			}
			foreach ($url_params['cid'] as $val) {
				$search['coll_ids'][] = $val;
			}
			$search['coll_ids'] = array_unique($search['coll_ids']);
		}
		//collection_ascii_id as param TRUMPS coll in get array
		if (isset($params['collection_ascii_id'])) {
			$search['coll_ids'] = array();
			//$search['coll_ids'][] = $params['collection_ascii_id'];
			$coll_ascii_id = $params['collection_ascii_id'];
		}
		//populate general find and omit array
		if (isset($url_params['q'])) {
			$query = is_array($url_params['q']) ? $url_params['q'][0] : $url_params['q'];
			foreach ($this->_tokenizeQuoted($query) as $t) {
				if ('-' == substr($t,0,1)) {
					$search['omit'][] = substr($t,1);
				} else {
					$search['find'][] = $t;
				}
			}
		}
		//for substring att searches
		foreach ($url_params as $k => $val) {
			if (!is_array($val)) {
				$val = array($val);
			}
			if (('q' != $k) && ('type' != $k) && strpos($k,'.')){
				$coll = null;
				$att = null;
				$tokens = array();
				list($coll,$att) = explode('.',$k);
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
		//for attr exact value searches (md5 hash of value_text)
		foreach ($url_params as $k => $val) {
			// for md5 hash only take one
			$val = is_array($val) ? $val[0] : $val;
			$coll = null;
			$att = null;
			if (('q' != $k) && strpos($k,':') && (!strpos($k,'.'))){
				list($coll,$att) = explode(':',$k);
				$search['att'][$coll][$att]['value_text_md5'] = array();
				$search['att'][$coll][$att]['value_text_md5'][] = $val;
				$search['att'][$coll][$att]['value_text_md5'] = array_unique($search['att'][$coll][$att]['value_text_md5']);
			}
		}
		//for item_type filter
		foreach ($url_params as $k => $val) {
			// for md5 hash only take one
			$val = is_array($val) ? $val[0] : $val;
			if (('type' == $k) && $val) {
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
		$this->search = $search;

		// DONE parsing search string!!
	}
	private function _tokenizeQuoted($string) {
		//from php.net:
		for($tokens=array(), $nextToken=strtok($string, ' '); $nextToken!==false; $nextToken=strtok(' ')) {
			if($nextToken{0}=='"')
				$nextToken = $nextToken{strlen($nextToken)-1}=='"' ?
					substr($nextToken, 1, -1) : substr($nextToken, 1) . ' ' . strtok('"');
			$tokens[] = $nextToken;
		}
		return $tokens;
	}
	private function _testArray($a,$key) {
		if ( isset($a[$key]) && is_array($a[$key]) && count($a[$key]) && isset($a[$key][0]) && $a[$key][0]) {
			return true;
		}
		return false;
	}
	private function _normalizeSearch($search) {
		$search_string = '';
		foreach(array('find','omit','or') as $key) {
			$search_string .= "$key:";
			if (isset($search[$key])) {
				$set = $search[$key];
				asort($set);
				$search_string .= join(',',$set) . ";";
			}
		}
		$att_array = $search['att'];
		$coll_array = array_keys($search['att']);
		asort($coll_array);
		foreach ($coll_array as $coll_name) {
			$att_names_array = array_keys($att_array[$coll_name]);
			asort($att_names_array);
			foreach ($att_names_array as $att_name) {
				foreach(array('find','omit','or','value_text_md5') as $key) {
					$set = array();
					if (isset($att_array[$coll_name][$att_name][$key])) {
						$set = $att_array[$coll_name][$att_name][$key];
						asort($set);
						if (count($set)) {
							$search_string .= "$coll_name.$att_name.$key:" .  join(',',$set) . ";";
						}
					}
				}
			}
		}
		if (isset($search['coll_ids'])) {
			$c_array = $search['coll_ids'];
			asort($c_array);
			$search_string .= "collections:" .  join(',',$c_array) . ";";
		}
		if (isset($search['type'])) {
			$search_string .= $search['type'];
		}
		return $search_string;
	}

	private function _createSql($search) {
		$search_table_sets = array();
		if (count($search['find'])) {
			foreach ($search['find'] as $k => $term) {
				$search['find'][$k] = "lower(value_text) LIKE '%". strtolower($term) . "%'";
			}
			$search_table_sets[] = join(' AND ',$search['find']);
		}
		if (count($search['omit'])) {
			foreach ($search['omit'] as $k => $term) {
				$search['omit'][$k] = "lower(value_text) NOT LIKE '%". strtolower($term) . "%'";
			}
			$search_table_sets[] = join(' AND ',$search['omit']);
		}
		if (count($search['or'])) {
			foreach ($search['or'] as $k => $term) {
				$search['or'][$k] = "lower(value_text) LIKE '%". strtolower($term) . "%'";
			}
			$search_table_sets[] = "(" . join(' OR ',$search['or']) . ")";
		}
		if (count($search_table_sets)) {
			$search_table_sql = "
				SELECT item_id 
				FROM search_table 
				WHERE " . join(' AND ', $search_table_sets);
		}

		$value_table_search_sets = array();
		foreach ($search['att'] as $coll => $att_arrays) {
			foreach ($search['att'][$coll] as $att => $ar) {
				$ar_table_sets = array();
				if ($this->_testArray($ar,'find')) {
					foreach ($ar['find'] as $k => $term) {
						$ar['find'][$k] = "lower(v.value_text) LIKE '%". strtolower($term) . "%'";
					}
					$ar_table_sets[] = join(' AND ',$ar['find']);
				}
				if ($this->_testArray($ar,'omit')) {
					foreach ($ar['omit'] as $k => $term) {
						$ar['omit'][$k] = "lower(v.value_text) NOT LIKE '%". strtolower($term) . "%'";
					}
					$ar_table_sets[] = join(' AND ',$ar['omit']);
				}
				if ($this->_testArray($ar,'or')) {
					foreach ($ar['or'] as $k => $term) {
						$ar['or'][$k] = "lower(v.value_text) LIKE '%". strtolower($term) . "%'";
					}
					$ar_table_sets[] = "(" . join(' OR ',$ar['or']) . ")";
				}
				if ($this->_testArray($ar,'value_text_md5')) {
					foreach ($ar['value_text_md5'] as $k => $term) {
						$ar['value_text_md5'][$k] = "v.value_text_md5 = '$term'";
					}
					$ar_table_sets[] = join(' AND ',$ar['value_text_md5']);
				}
				if (count($ar_table_sets)) {
					$value_table_search_sets[] = "
						id IN (
							SELECT v.item_id FROM value v,collection c,attribute a
							WHERE c.ascii_id = '$coll'
							AND a.ascii_id = '$att'
							AND a.collection_id = c.id
							AND v.attribute_id = a.id
							AND " . join(' AND ', $ar_table_sets)
							. ")";
				}
			}
			unset($ar_table_sets);
		}
		if (count($search['coll_ids']) && isset($search_table_sql)) {
			$cid_set = join(",",$search['coll_ids']);
			$search_table_sql .= " AND collection_id IN ($cid_set)";
		}
		if (isset($search_table_sql) && count($value_table_search_sets)) {
			$sql = "
				SELECT id, collection_id FROM item
				WHERE id IN ($search_table_sql)
				AND " . join(' AND ',$value_table_search_sets);
		} elseif (isset($search_table_sql)) {
			$sql = "
				SELECT id, collection_id FROM item
				WHERE id IN ($search_table_sql)";
		} elseif (count($value_table_search_sets)) {
			$sql = "
				SELECT id, collection_id FROM item
				WHERE " . join(' AND ',$value_table_search_sets);
		} else {
			return 'no query';
		}
		if (isset($search['type']['coll']) && isset($search['type']['name'])) {
			$sql .=" 
				AND WHERE item_type_id IN
				(SELECT id FROM item_type
				WHERE ascii_id = '{$search['type']['name']}'
				AND collection_id IN (SELECT id
				FROM collection WHERE ascii_id = '{$search['type']['coll']}'))
				";
		}
		return $sql;
	}

	public function printDiagnostics() {
		print $this->_createSql($this->search);
		print('<pre>');
		print_r($search);
		print('</pre>');
		print md5($this->_normalizeSearch($search));
	}

	private function _executeSearch($hash) {
		$collection_lookup = Dase_DB_Collection::getLookupArray('collection_name');
		$db = Dase_DB::get();
		$sql = $this->_createSql($this->search);	
		$st = $db->prepare($sql);	
		$st->execute();
		$result = array();
		$result['tallies'] = array();
		$result['item_ids'] = array();
		$items = array();
		while ($row = $st->fetch()) {
			$name = $collection_lookup[$row['collection_id']];
			$items[$name][] = $row['id'];
		}
		uasort($items, array('Dase_Util','sortByCount'));
		foreach ($items as $coll => $set) {
			$result['tallies'][$coll] = count($set);
			$result['item_ids'] = array_merge($result['item_ids'],$set);;
		}
		$result['hash'] = $hash;
		$result['count'] = count($result['item_ids']);
		$result['search'] = $this->search;
		$result['sql'] = $sql;
		return $result;
	}

	public function getResult() {
		$result = array();
		$cache = new Dase_DB_SearchCache();
		$hash = md5($this->_normalizeSearch($this->search));
		$cache->search_md5 = $hash;
		$cache->refine = 'newdase'; 
		if (!$cache->findOne()) {
			$result = $this->_executeSearch($hash);
			$cache->item_id_string = serialize($result); 
			$cache->search_md5 = $hash; 
			$cache->refine = 'newdase'; 
			$cache->insert();
		} else {
			$result = unserialize($cache->item_id_string);
		}
		return $result;
	}
}

