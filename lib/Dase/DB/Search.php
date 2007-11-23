<? 
class Dase_DB_Search {

	public $search;		

	function __construct($params) {
		$url_params = Dase::instance()->url_params;
		$search['type'] = null;
		$search['colls'] = array();
		$search['omit_colls'] = array();
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
		// for attribute searches, name is '<coll_ascii_id>%<attribute_ascii_id>'
		// use percent (as in example) for auto substring/phrase search
		// use single colon to match md5 hash of exact value_text string
		// use single period to match exact value_text string (case-insensitive)
		// add more attribute searches (refinements) by adding them to
		// the query string. Note that the use of '.' or ':' or '%' in
		// a query parameter name that is NOT part of the search will
		// make the search fail (since it'll be interpreted as an
		// attribute search

		if (isset($url_params['c'])) {
			if (!is_array($url_params['c'])) {
				$url_params['c'] = array($url_params['c']);
			}
			foreach ($url_params['c'] as $c) {
				$search['colls'][] = "'$c'";
			}
			$search['colls'] = array_unique($search['colls']);
		}
		//url parameter 'nc' means "NOT collection..."
		if (isset($url_params['nc'])) {
			if (!is_array($url_params['nc'])) {
				$url_params['nc'] = array($url_params['nc']);
			}
			foreach ($url_params['nc'] as $nc) {
				$search['omit_colls'][] = "'$nc'";
			}
			$search['omit_colls'] = array_unique($search['omit_colls']);
		}
		//collection_ascii_id as param TRUMPS coll in get array
		//can come from request url
		if (isset($params['collection_ascii_id'])) {
			$collection_ascii_id = $params['collection_ascii_id'];
			$search['colls'] = array("'$collection_ascii_id'");
			$echo['collection_ascii_id'] = $collection_ascii_id;
		}
		//OR query string
		if (isset($url_params['collection_ascii_id'])) {
			if (is_array($url_params['collection_ascii_id'])) {
				//take the last one
				$collection_ascii_id = array_pop($url_params['collection_ascii_id']);
			} else {
				$collection_ascii_id = $url_params['collection_ascii_id'];
			}
			if ($collection_ascii_id) {
				$search['colls'] = array("'$collection_ascii_id'");
				$echo['collection_ascii_id'] = $collection_ascii_id;
			}
		}
		//populate general find and omit array
		if (isset($url_params['q'])) {
			$query = $url_params['q'];
			if (!is_array($query)) {
				$query = array($query);
			}
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
		}
		//for substring att searches  => att%val
		foreach ($url_params as $k => $val) {
			if (!is_array($val)) {
				$val = array($val);
			}
			if (('q' != $k) && ('type' != $k) && strpos($k,'%')){
				//$echo['sub'][$k] = join(' ',$val);
				$echo['sub'][$k] = $val;
				$coll = null;
				$att = null;
				$tokens = array();
				list($coll,$att) = explode('%',$k);
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
		foreach ($url_params as $k => $val) {
			if (!is_array($val)) {
				$val = array($val);
			}
			foreach($val as $v) {
				$coll = null;
				$att = null;
				if (strpos($k,'.') && !strpos($k,'%') && !strpos($k,':')){
					list($coll,$att) = explode('.',$k);
					$echo['exact'][$k][] = $v;
					$search['att'][$coll][$att]['value_text'] = array();
					$search['att'][$coll][$att]['value_text'][] = $v;
					$search['att'][$coll][$att]['value_text'] = array_unique($search['att'][$coll][$att]['value_text']);
				}
			}
		}

		//for attr exact value md5 searches (md5 hash of value_text) => att:val
		foreach ($url_params as $k => $val) {
			if (!is_array($val)) {
				$val = array($val);
			}
			$coll = null;
			$att = null;
			foreach($val as $v) {
				if (strpos($k,':') && !strpos($k,'.') && !strpos($k,'%')){
					list($coll,$att) = explode(':',$k);
					$echo['exact'][$k][] = Dase_DB_Value::getValueTextByHash($coll,$v);
					$search['att'][$coll][$att]['value_text_md5'] = array();
					$search['att'][$coll][$att]['value_text_md5'][] = $v;
					$search['att'][$coll][$att]['value_text_md5'] = array_unique($search['att'][$coll][$att]['value_text_md5']);
				}
			}
		}

		//for item_type filter
		foreach ($url_params as $k => $val) {
			// for item type only take ONE 
			$val = is_array($val) ? $val[0] : $val;
			if (('type' == $k) && $val) {
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

		//construct echo
		$echo_str = '';
		if ($echo['query']) {
			$echo_str .= " {$echo['query']} ";
		} 
		if ($echo['exact']) {
			$echo_arr = array();
			foreach ($echo['exact'] as $k => $v) {
				foreach( $v as $val) {
					$echo_arr[] = "$val in $k";
				}
			}
			if ($echo_str) {
				$echo_str .= " AND ";
			}
			$echo_str .= join(' AND ',$echo_arr);
		}
		if ($echo['sub']) {
			$echo_arr = array();
			foreach ($echo['sub'] as $k => $v) {
				foreach( $v as $val) {
				$echo_arr[] = "$val in $k";
				}
			}
			if ($echo_str) {
				$echo_str .= " AND ";
			}
			$echo_str .= join(' AND ',$echo_arr);
		}
		if ($echo['collection_ascii_id']) {
			$echo_str .= " in {$echo['collection_ascii_id']} ";
		}
		if ($echo['type']) {
			if ($echo_str) {
				$echo_str .= " WITH ";
			}
			$echo_str .= " item type {$echo['type']} ";
		}
		$search['echo'] = $echo_str;
		$this->search = $search;

		// DONE parsing search string!!
	}

	public static function get($params) {
		return new Dase_DB_Search($params);
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
				foreach(array('find','omit','or','value_text_md5','value_text') as $key) {
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
		if (isset($search['colls'])) {
			$c_array = $search['colls'];
			asort($c_array);
			$search_string .= "collections:" .  join(',',$c_array) . ";";
		}
		if (isset($search['omit_colls'])) {
			$nc_array = $search['omit_colls'];
			asort($nc_array);
			$search_string .= "omit_collections:" .  join(',',$nc_array) . ";";
		}
		if (isset($search['type']) && isset($search['type']['coll']) && isset($search['type']['name'])) {
			$search_string .= 'type=:' .  $search['type']['coll'] . $search['type']['name'];
		}
		return $search_string;
	}

	private function _createSql($search) {
		//compile sql for queries of search_table (i.e. search index)
		$search_table_sets = array();
		if (count($search['find'])) {
			//the key needs to be specified to make sure it overwrites 
			//(rather than appends) to the array
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

		//compile sql for queries of value table 
		$value_table_search_sets = array();
		foreach ($search['att'] as $coll => $att_arrays) {
			foreach ($att_arrays as $att => $ar) {
				$ar_table_sets = array();
				if ($this->_testArray($ar,'find')) {
					//the key needs to be specified to make sure it overwrites 
					//(rather than appends) to the array
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
				if ($this->_testArray($ar,'value_text')) {
					foreach ($ar['value_text'] as $k => $term) {
						//note that exact searches are CASE INSENSITIVE
						$ar['value_text'][$k] = "lower(v.value_text) = '" . strtolower($term) . "'";
					}
					$ar_table_sets[] = join(' AND ',$ar['value_text']);
				}
				if ($this->_testArray($ar,'value_text_md5')) {
					foreach ($ar['value_text_md5'] as $k => $term) {
						$ar['value_text_md5'][$k] = "v.value_text_md5 = '$term'";
					}
					$ar_table_sets[] = join(' AND ',$ar['value_text_md5']);
				}
				if (count($ar_table_sets)) {
					if (false === strpos($att,'admin_')) {
						$value_table_search_sets[] = "
							id IN (
								SELECT v.item_id FROM value v,collection c,attribute a
								WHERE c.ascii_id = '$coll'
								AND a.ascii_id = '$att'
								AND a.collection_id = c.id
								AND v.attribute_id = a.id
								AND " . join(' AND ', $ar_table_sets)
								. ")";
					} else {
						$value_table_search_sets[] = "
							id IN (
								SELECT v.item_id FROM value v,collection c,attribute a
								WHERE c.ascii_id = '$coll'
								AND a.ascii_id = '$att'
								AND a.collection_id = 0
								AND v.attribute_id = a.id
								AND " . join(' AND ', $ar_table_sets)
								. ")";
					}
				}
			}
			unset($ar_table_sets);
		}
		if (count($search['colls']) && isset($search_table_sql)) {
			$ascii_ids = join(",",$search['colls']);
			$search_table_sql .= " AND collection_id IN (SELECT id FROM collection WHERE ascii_id IN ($ascii_ids))";
		}
		//if not explicitly requested, non-public collecitons will be omitted
		if (!count($search['colls']) && isset($search_table_sql)) {
			//make sure this boolean query is portable!!!
			$search_table_sql .= " AND collection_id IN (SELECT id FROM collection WHERE is_public = '1')";
		}
		if (count($search['omit_colls']) && isset($search_table_sql)) {
			$ascii_ids = join(",",$search['omit_colls']);
			$search_table_sql .= " AND collection_id NOT IN (SELECT id FROM collection WHERE ascii_id IN ($ascii_ids))";
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
		//if searching ONLY for item type (NOT simply as filter)
		//as indicated by lack of other queries (i.e., we got to this point in decision tree)
		} elseif (isset($search['type']['coll']) && isset($search['type']['name'])) {
			$sql =" 
				SELECT id, collection_id FROM item
				WHERE item_type_id IN
				(SELECT id FROM item_type
				WHERE ascii_id = '{$search['type']['name']}'
				AND collection_id IN (SELECT id
				FROM collection WHERE ascii_id = '{$search['type']['coll']}'))
				";
		} else {
			$sql = 'no query';
		}
		//if search type is used as filter:
		if (isset($search['type']['coll']) && isset($search['type']['name']) && 
			(isset($search_table_sql) || count($value_table_search_sets))) {
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
		$collection_lookup = Dase_DB_Collection::getLookupArray();
		$db = Dase_DB::get();
		$sql = $this->_createSql($this->search);	
		$st = $db->prepare($sql);	
		$st->execute();
		$result = array();
		$result['tallies'] = array();
		$result['item_ids'] = array();
		$items = array();
		while ($row = $st->fetch()) {
			$items[$row['collection_id']][] = $row['id'];
		}
		uasort($items, array('Dase_Util','sortByCount'));
		foreach ($items as $coll_id => $set) {
			$ascii_id = $collection_lookup[$coll_id]['ascii_id'];
			$name = $collection_lookup[$coll_id]['collection_name'];
			$result['tallies'][$ascii_id]['total'] = count($set);
			$result['tallies'][$ascii_id]['name'] = $name;
			$result['item_ids'] = array_merge($result['item_ids'],$set);
		}
		$result['hash'] = $hash;
		$result['count'] = count($result['item_ids']);
		$result['search'] = $this->search;
		$result['sql'] = $sql;
		$result['link'] = $this->getLink();
		$result['echo'] = $this->search['echo'];
		$result['request_url'] = Dase::instance()->request_url;
		$result['query_string'] = Dase::instance()->query_string;
		return $result;
	}

	public function getLink() {
		$link = '';
		$url_params = Dase::instance()->url_params;
		foreach ($url_params as $k => $v) {
			if (is_array($v)) {
				foreach($v as $val) {
					if (!in_array($val,array('max','start'))) {
						$link .= "&$k=$val";
					}
				}
			} else {
				if (!in_array($v,array('max','start'))) {
					$link .= "&$k=$v";
				}
			}
		}
		return "search?" . $link;
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
			$cache->timestamp = date(DATE_ATOM,time()); 
			$cache->insert();
		} else {
			$result = unserialize($cache->item_id_string);
		}
		$result['timestamp'] = $cache->timestamp;
		$result['hash'] = $hash;
		return $result;
	}

	public static function getResultByHash($hash) {
		$result = array();
		$cache = new Dase_DB_SearchCache();
		$cache->search_md5 = $hash;
		if ($cache->findOne()) {
		$result = unserialize($cache->item_id_string);
		$result['timestamp'] = $cache->timestamp;
		$result['hash'] = $cache->search_md5;
		}
		return $result;
	}
}

