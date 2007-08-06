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
		$set = array();
		list($set['coll'],$set['att']) = explode('::',$k);
		if (isset($set['coll']) && isset($set['att'])) {
			foreach ($val_array as $v) {
				foreach (tokenizeQuoted($v) as $t) {
					$tokens[] = $t;
				}
				$set['find'] = array();
				$set['omit'] = array();
				$set['or'] = array();
				foreach ($tokens as $tok) {
					if ('-' == substr($tok,0,1)) {
						$set['omit'][] = substr($tok,1);
					} else {
						$set['find'][] = $tok;
					}
				}
			}
			$or_keys = array_keys($set['find'],'or');
			foreach ($or_keys as $or_key) {
				foreach(array($or_key-1,$or_key,$or_key+1) as $k) {
					if (array_key_exists($k,$set['find'])) {
						//first assign to 'or' array (use hash to get rid of duplicates)
						$set['or'][$set['find'][$k]] = 1;
					}
				}
			}
			//purge the find array of all tokens in the or array
			foreach ($set['or'] as $k => $v) {
				while (false !== array_search($k,$set['find'])) {
					$remove_key = array_search($k,$set['find']);
					unset($set['find'][$remove_key]);
				}
			}
			unset($set['or']['or']);
			$search['attribute'][] = $set;
		}
	}
}

foreach ($get_arrays as $k => $v) {
	if (('q' != $k) && strpos($k,':') && (!strpos($k,'::'))){
		$set = array();
		list($set['coll'],$set['att']) = explode(':',$k);
		$set['value_text'] = $v;
		$search['attribute'][] = $set;
	}
}

$or_keys = array_keys($search['find'],'or');

foreach ($or_keys as $or_key) {
	foreach(array($or_key-1,$or_key,$or_key+1) as $k) {
		if (array_key_exists($k,$search['find'])) {
			//first assign to 'or' array (use hash to get rid of duplicates)
			$search['or'][$search['find'][$k]] = 1;
		}
	}
}

foreach ($search['or'] as $k => $v) {
	while (false !== array_search($k,$search['find'])) {
		$remove_key = array_search($k,$search['find']);
		unset($search['find'][$remove_key]);
	}
}

unset($search['or']['or']);


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

//look for
//1. sets of "OR'ed" words or phrases
//2. "NOT" words or phrases
//exact searches
//2. individual 'not' terms (preceded by '-')
//3. sets of 'OR' terms
//4
//create a search stack?????????????
//id first new search
//use a refine box??  OR echo search in an input box....
//
//daseql 
//
//dase/{collectionAsciiId?}/search?q[]={searchTerms}&{collectionAsciiId}:{attributeAsciiId}?}={exactSearchPhrase}
//
//

