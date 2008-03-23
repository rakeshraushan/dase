<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 

class Dase_Search 
{

	public $search;		
	public $bound_params = array();
	private $url_params;
	private $request_uri;
	private $query_string;

	public static function get($request_uri,$query_string)
	{
		$search_obj = new Dase_Search();
		$search_obj->parse($request_uri,$query_string);
		return $search_obj;
	}

	public static function parseQueryString($query_string)
	{
		//split params into key value pairs AND allow multiple 
		//params w/ same key as an array (like standard CGI)
		$url_params = array();
		//NOTE: urldecode is NOT UTF-8 compatible
		$qs = html_entity_decode(urldecode($query_string));
		$pairs = explode('&',$qs);
		if (count($pairs)) {
			foreach ($pairs as $pair) {
				if (false !== strpos($pair,'=')) {	
					list($key,$val) = explode('=',$pair);
					if (!isset($url_params[$key])) {
						//not an array
						$url_params[$key] = $val;
					} elseif(is_array($url_params[$key])) {
						//IS an array
						$url_params[$key][] = $val;
					} else {
						//key is set, but it is NOT an array, so make it one!!
						$temp = $url_params[$key];
						$url_params[$key] = array();
						$url_params[$key][] = $temp;
						$url_params[$key][] = $val;
					}
				}
			}
		}
		return $url_params;
	}

	public static function getCollectionAsciiId($request_uri)
	{
		//NOTE: this ASSUMES the uri pattern for collection
		if (preg_match('/collection\/([^\/]*)\/search/',$request_uri,$matches)) {
			$collection_ascii_id = $matches[1];
			return $collection_ascii_id;
		} else {
			return false;
		}
	}

	function parse($request_uri,$query_string)
	{
		$url_params = Dase_Search::parseQueryString($query_string);
		$this->url_params = $url_params;
		$this->request_uri = $request_uri;
		$this->query_string = $query_string;
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
		// for attribute searches, name is '<coll_ascii_id>~<attribute_ascii_id>'
		// use tilde (as in example) for auto substring/phrase search
		// use single colon to match md5 hash of exact value_text string
		// use single period to match exact value_text string (case-insensitive)
		// add more attribute searches (refinements) by adding them to
		// the query string. Note that the use of '.' or ':' or '~' in
		// a query parameter name that is NOT part of the search will
		// make the search fail (since it'll be interpreted as an
		// attribute search
		/*
		 * exact match:
		 * test.title=farewell+to+arms 
		 *
		 * match hash:
		 * test:title=5045aca392ed260667b8489bfe7ccc03
		 *
		 * match substring:
		 * test~title=farewell+to+a
		 *
		 * match item_type:
		 * type=test:picture
		 */

		if (isset($url_params['c'])) {
			if (!is_array($url_params['c'])) {
				$url_params['c'] = array($url_params['c']);
			}
			foreach ($url_params['c'] as $c) {
				$search['colls'][] = $c;
			}
			$search['colls'] = array_unique($search['colls']);
		}
		//url parameter 'nc' means "NOT collection..."
		if (isset($url_params['nc'])) {
			if (!is_array($url_params['nc'])) {
				$url_params['nc'] = array($url_params['nc']);
			}
			foreach ($url_params['nc'] as $nc) {
				$search['omit_colls'][] = $nc;
			}
			$search['omit_colls'] = array_unique($search['omit_colls']);
		}

		//collection_ascii_id in uri_string trumps coll in get array
		$collection_ascii_id = Dase_Search::getCollectionAsciiId($request_uri);

		//'collection_ascii_id' in query string trumps both
		if (isset($url_params['collection_ascii_id'])) {
			if (is_array($url_params['collection_ascii_id'])) {
				//take the last one
				$collection_ascii_id = array_pop($url_params['collection_ascii_id']);
			} else {
				$collection_ascii_id = $url_params['collection_ascii_id'];
			}
		}
		if ($collection_ascii_id) {
			$search['colls'] = array($collection_ascii_id);
			$echo['collection_ascii_id'] = $collection_ascii_id;
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
		//for substring att searches  => att~val
		foreach ($url_params as $k => $val) {
			if (!is_array($val)) {
				$val = array($val);
			}
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
		foreach ($url_params as $k => $val) {
			if (!is_array($val)) {
				$val = array($val);
			}
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

		//for attr exact value md5 searches (md5 hash of value_text) => att:val
		foreach ($url_params as $k => $val) {
			if (!is_array($val)) {
				$val = array($val);
			}
			$coll = null;
			$att = null;
			foreach($val as $v) {
				if (strpos($k,':') && !strpos($k,'.') && !strpos($k,'~')){
					list($coll,$att) = explode(':',$k);
					//do NOT make db call in this method! it is a waste if search is db cached
					//echo should be "calculated" upon cache miss
					//$echo['exact'][$k][] = Dase_DBO_Value::getValueTextByHash($coll,$v);
					$echo['hash'][] = array('coll' => $coll,'k' => $k,'v' => $v);
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

		$search['echo'] = $echo;
		$this->search = $search;

		// DONE parsing search string!!
	}

	public static function constructEcho($echo) {
		//construct echo
		if (isset($echo['hash']) && is_array($echo['hash'])) {
			foreach ($echo['hash'] as $set) {
				$echo['exact'][$set['k']][] = Dase_DBO_Value::getValueTextByHash($set['coll'],$set['v']);
			}
		}
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
		return $echo_str;
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

	private function _normalizeSearch($search)
	{
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

	public function createSql()
	{
		$search = $this->search;
		$search_table_params = array();
		$value_table_params = array();

		//compile sql for queries of search_table (i.e. search index)
		$search_table_sets = array();
		if (count($search['find'])) {
			//the key needs to be specified 
			//(it is just the number index) to make sure it overwrites 
			//(rather than appends) to the array
			foreach ($search['find'] as $k => $term) {
				$search['find'][$k] = "lower(value_text) LIKE ?";
				$search_table_params[] = "%".strtolower($term)."%";
			}
			$search_table_sets[] = join(' AND ',$search['find']);
		}
		if (count($search['omit'])) {
			foreach ($search['omit'] as $k => $term) {
				$search['omit'][$k] = "lower(value_text) NOT LIKE ?";
				$search_table_params[] = "%".strtolower($term)."%";
			}
			$search_table_sets[] = join(' AND ',$search['omit']);
		}
		if (count($search['or'])) {
			foreach ($search['or'] as $k => $term) {
				$search['or'][$k] = "lower(value_text) LIKE ?";
				$search_table_params[] = "%".strtolower($term)."%";
			}
			$search_table_sets[] = "(" . join(' OR ',$search['or']) . ")";
		}
		if (count($search_table_sets)) {
			$search_table_sql = "SELECT item_id FROM search_table WHERE " . join(' AND ', $search_table_sets);
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
				if ($this->_testArray($ar,'value_text_md5')) {
					foreach ($ar['value_text_md5'] as $k => $term) {
						$ar['value_text_md5'][$k] = "v.value_text_md5 = ?";
						$value_table_params[] = $term;
					}
					$ar_table_sets[] = join(' AND ',$ar['value_text_md5']);
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
			$search_table_sql .= " AND collection_id IN (SELECT id FROM collection WHERE is_public = '1')";
		}
		if (count($search['omit_colls']) && isset($search_table_sql)) {
			foreach ($search['omit_colls'] as $ccc) {
				$placeholders[] = '?'; 
			}
			$ph = join(",",$placeholders);
			unset($placeholders);
			$search_table_params = array_merge($search_table_params,$search['omit_colls']);
			$search_table_sql .= " AND collection_id NOT IN (SELECT id FROM collection WHERE ascii_id IN ($ph))";
		}
		if (isset($search_table_sql) && count($value_table_search_sets)) {
			$sql = 
				"SELECT id, collection_id FROM item WHERE id IN ($search_table_sql) AND " . join(' AND ',$value_table_search_sets);
			$this->bound_params = array_merge($this->bound_params,$search_table_params);
			$this->bound_params = array_merge($this->bound_params,$value_table_params);
		} elseif (isset($search_table_sql)) {
			$sql = 
				"SELECT id, collection_id FROM item WHERE id IN ($search_table_sql)";
			$this->bound_params = array_merge($this->bound_params,$search_table_params);
		} elseif (count($value_table_search_sets)) {
			$sql = 
				"SELECT id, collection_id FROM item WHERE " . join(' AND ',$value_table_search_sets);
			$this->bound_params = array_merge($this->bound_params,$value_table_params);
			//if searching ONLY for item type (NOT simply as filter)
			//as indicated by lack of other queries (i.e., we got to this point in decision tree)
		} elseif (isset($search['type']['coll']) && isset($search['type']['name'])) {
			$sql =
				"SELECT id, collection_id FROM item WHERE item_type_id IN (SELECT id FROM item_type WHERE ascii_id = ? AND collection_id IN (SELECT id FROM collection WHERE ascii_id = ?))";
			$this->bound_params[] = $search['type']['name'];
			$this->bound_params[] = $search['type']['coll'];
		} else {
			$sql = 'no query';
		}
		//if search type is used as filter:
		if (isset($search['type']['coll']) && isset($search['type']['name']) && 
			(isset($search_table_sql) || count($value_table_search_sets))) {
				$sql .=
					"AND WHERE item_type_id IN (SELECT id FROM item_type WHERE ascii_id = ? AND collection_id IN (SELECT id FROM collection WHERE ascii_id = ?))";
				$this->bound_params[] = $search['type']['name'];
				$this->bound_params[] = $search['type']['coll'];
			}
		return $sql;
	}

	private function _executeSearch($hash)
	{
		$collection_lookup = Dase_DBO_Collection::getLookupArray();
		$db = Dase_DB::get();
		$sql = $this->createSql();	
		if (defined('DEBUG')) {
			Dase_Log::put('sql',$sql);
			Dase_Log::put('sql',join(', ',$this->bound_params));
		}
		$st = $db->prepare($sql);	
		$st->execute($this->bound_params);
		$result = array();
		$result['tallies'] = array();
		$result['item_ids'] = array();
		$items = array();
		//create hit tally per collection:
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
		$result['link'] = $this->getLink($this->query_string);
		$result['echo'] = $this->search['echo'];
		$result['request_url'] = $this->request_uri;
		$result['query_string'] = $this->query_string;
		return $result;
	}

	public function getLink($query_string)
	{
		$link = '';
		$url_params = Dase_Search::parseQueryString($query_string);;
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

	public function getResult()
	{
		//by the way, no easy way to grab a 
		//particular user's recent searches
		$result = array();
		$cache = new Dase_DBO_SearchCache();
		$hash = md5($this->_normalizeSearch($this->search));
		$cache->search_md5 = $hash;
		$cache->refine = 'newdase'; 
		if (!$cache->findOne()) {
			$result = $this->_executeSearch($hash);
			$result['echo'] = Dase_Search::constructEcho($result['echo']);
			//for backward compatibilty this is called
			//item_id_string, but it is actually the
			//complete result data structure
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

}

