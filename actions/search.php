<?php
$get_arrays = Dase::filterGetArray();
$tokens = array();

$search['att'] = array();
$search['find'] = array();
$search['omit'] = array();
$search['or'] = array();

//populate general find and omit array
if (testArray($get_arrays,'q')) {
	foreach ($get_arrays['q'] as $query) {
		foreach (tokenizeQuoted($query) as $t) {
			if ('-' == substr($t,0,1)) {
				$search['omit'][] = substr($t,1);
			} else {
				$search['find'][] = $t;
			}
		}
	}
}

//for substring att searches
foreach ($get_arrays as $k => $val_array) {
	if (('q' != $k) && strpos($k,'::')){
		$coll = null;
		$att = null;
		$tokens = array();
		list($coll,$att) = explode('::',$k);
		if ($coll && $att) {
			$search['att'][$coll][$att]['find'] = array();
			$search['att'][$coll][$att]['omit'] = array();
			$search['att'][$coll][$att]['or'] = array();
			foreach ($val_array as $k => $v) {
				foreach (tokenizeQuoted($v) as $t) {
					$tokens[] = $t;
				}
				foreach ($tokens as $tok) {
					if ('-' == substr($tok,0,1)) {
						$search['att'][$coll][$att]['omit'][] = substr($tok,1);
					} else {
						$search['att'][$coll][$att]['find'][] = $tok;
					}
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

//for attr exact value searches
foreach ($get_arrays as $k => $val) {
	$coll = null;
	$att = null;
	if (('q' != $k) && strpos($k,':') && (!strpos($k,'::'))){
		list($coll,$att) = explode(':',$k);
		$search['att'][$coll][$att]['value_text_md5'] = array();
		foreach ($val as $v) {
			$search['att'][$coll][$att]['value_text_md5'][] = $v;
		}
		$search['att'][$coll][$att]['value_text_md5'] = array_unique($search['att'][$coll][$att]['value_text_md5']);
	}
}

$or_keys = array_keys($search['find'],'or');
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

print("<pre>");
print_r($search);
print("</pre>");

//from php.net:
function tokenizeQuoted($string) {
	for($tokens=array(), $nextToken=strtok($string, ' '); $nextToken!==false; $nextToken=strtok(' ')) {
		if($nextToken{0}=='"')
			$nextToken = $nextToken{strlen($nextToken)-1}=='"' ?
				substr($nextToken, 1, -1) : substr($nextToken, 1) . ' ' . strtok('"');
		$tokens[] = $nextToken;
	}
	return $tokens;
}

function testArray($a,$key) {
	if ( isset($a[$key]) && is_array($a[$key]) && count($a[$key]) && isset($a[$key][0]) && $a[$key][0]) {
		return true;
	}
	return false;
}


function normalizeSearch($search) {
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
	return $search_string;
}

function createSql($search) {
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
	$search_table_sql = "
		SELECT item_id 
		FROM search_table 
		WHERE " . join(' AND ', $search_table_sets);

	$value_table_search_sets = array();
	foreach ($search['att'] as $coll => $att_arrays) {
		foreach ($search['att'][$coll] as $att => $ar) {
			$ar_table_sets = array();
			if (testArray($ar,'find')) {
				foreach ($ar['find'] as $k => $term) {
					$ar['find'][$k] = "lower(value_text) LIKE '%". strtolower($term) . "%'";
				}
				$ar_table_sets[] = join(' AND ',$ar['find']);
			}
			if (testArray($ar,'omit')) {
				foreach ($ar['omit'] as $k => $term) {
					$ar['omit'][$k] = "lower(value_text) NOT LIKE '%". strtolower($term) . "%'";
				}
				$ar_table_sets[] = join(' AND ',$ar['omit']);
			}
			if (testArray($ar,'or')) {
				foreach ($ar['or'] as $k => $term) {
					$ar['or'][$k] = "lower(value_text) LIKE '%". strtolower($term) . "%'";
				}
				$ar_table_sets[] = "(" . join(' OR ',$ar['or']) . ")";
			}
			if (testArray($ar,'value_text_md5')) {
				foreach ($ar['value_text_md5'] as $k => $term) {
					$ar['value_text_md5'][$k] = "value_text_md5 = '$term'";
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
						AND value.attribute_id = a.id
						AND " . join(' AND ', $ar_table_sets)
						. ")";
			}
		}
		unset($ar_table_sets);
	}
	if ($search_table_sql && count($value_table_search_sets)) {
		$sql = "
			SELECT id FROM item
			WHERE id IN ($search_table_sql)
			AND " . join(' AND ',$value_table_search_sets);
	} elseif ($search_table_sql) {
		$sql = "
			SELECT id FROM item
			WHERE id IN ($search_table_sql)";
	} elseif (count($value_table_search_sets)) {
		$sql = "
			SELECT id FROM item
			WHERE " . join(' AND ',$value_table_search_sets);
	} else {
		$sql = 'no query';
	}

	return $sql;
}

//print md5(normalizeSearch($search));

print createSql($search);

exit;
