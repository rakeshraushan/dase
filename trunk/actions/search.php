<?php
$get_arrays = Dase::filterGetArray();
$tokens = array();

if (!is_array($get_arrays['q'])) {
	throw new Exception('q is not an array');
}

$search['attribute'] = array();
$search['find'] = array();
$search['omit'] = array();
$search['or'] = array();

foreach ($get_arrays['q'] as $query) {
	foreach (tokenizeQuoted($query) as $t) {
		if ('-' == substr($t,0,1)) {
			$search['omit'][] = substr($t,1);
		} else {
			$search['find'][] = $t;
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
			foreach ($val_array as $v) {
				foreach (tokenizeQuoted($v) as $t) {
					$tokens[] = $t;
				}
				foreach ($tokens as $tok) {
					if ('-' == substr($tok,0,1)) {
						$search['attribute'][$coll][$att]['omit'][] = substr($tok,1);
					} else {
						$search['attribute'][$coll][$att]['find'][] = $tok;
					}
				}
			}
			$or_keys = array_keys($search['attribute'][$coll][$att]['find'],'or');
			if (!isset($search['attribute'][$coll][$att]['or'])) {
				$search['attribute'][$coll][$att]['or'] = array();
			}
			foreach ($or_keys as $or_key) {
				foreach(array($or_key-1,$or_key,$or_key+1) as $k) {
					if (array_key_exists($k,$search['attribute'][$coll][$att]['find'])) {
						if (!array_search($search['attribute'][$coll][$att]['find'][$k],$search['attribute'][$coll][$att]['or'])) {
							$search['attribute'][$coll][$att]['or'][] = $search['attribute'][$coll][$att]['find'][$k];
						}
					}
				}
			}
			//purge the find array of all tokens in the or array
			if (isset($search['attribute'][$coll][$att]['or'])) {
				foreach ($search['attribute'][$coll][$att]['or'] as $k => $v) {
					while (false !== array_search($v,$search['attribute'][$coll][$att]['find'])) {
						$remove_key = array_search($v,$search['attribute'][$coll][$att]['find']);
						unset($search['attribute'][$coll][$att]['find'][$remove_key]);
					}
				}
			}
			if (isset($search['attribute'][$coll][$att]['or'])) {
				while (false !== array_search('or',$search['attribute'][$coll][$att]['or'])) {
					$remove_key = array_search('or',$search['attribute'][$coll][$att]['or']);
					unset($search['attribute'][$coll][$att]['or'][$remove_key]);
				}
			}
		}
	}
}

foreach ($get_arrays as $k => $val) {
	$coll = null;
	$att = null;
	if (('q' != $k) && strpos($k,':') && (!strpos($k,'::'))){
		list($coll,$att) = explode(':',$k);
		foreach ($val as $v) {
			$search['attribute'][$coll][$att]['value_text'][] = $v;
		}
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
}

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




exit;
