<?php

if (!isset($argv[1])) {
		print "\n";
		print "ERROR: missing field name\n";
		print "\n";
		print "syntax: show_values.php <field_name>\n";
		print "\n";
		exit;
}


$data = json_decode(file_get_contents('dump.json'),1);


$att_ascii = $argv[1];


$values = array();

foreach ($data['items'] as $item) {
		foreach ($item['metadata'] as $k =>$v_arr) {
				if ($att_ascii == $k) {
						foreach($v_arr as $v) {
								$values[] = $v;
						}
				}
		}
}

foreach ($values as $v) {
		print $v."\n";
}
