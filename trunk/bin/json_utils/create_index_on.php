<?php

if (!isset($argv[1])) {
		print "\n";
		print "ERROR: missing field name\n";
		print "\n";
		print "syntax: create_index_on.php <field_name>\n";
		print "\n";
		exit;
}


$data = json_decode(file_get_contents('dump.json'),1);


$att_ascii = $argv[1];


$lookup_list = array();
$items = array();

foreach ($data['items'] as $item) {
		$items[$item['item_unique']] = array();
		foreach ($item['metadata'] as $k =>$v_arr) {
				if ($att_ascii == $k) {
						foreach($v_arr as $v) {
								if (!isset($lookup_list[$k])) {
										$lookup_list[$k] = array();
								}
								if (!isset($lookup_list[$k][$v])) {
										$lookup_list[$k][$v] = array();
								}
								$lookup_list[$k][$v][] = $item['item_unique'];
						}
				}
		}
		unset($item['metadata_extended']);
		unset($item['links']);
		$items[$item['item_unique']] = $item;
}

$index['lookup_list'] = $lookup_list;
$index['items'] = $items;

$json_index = json_encode($index);

file_put_contents($att_ascii.'_index.json',$json_index);
