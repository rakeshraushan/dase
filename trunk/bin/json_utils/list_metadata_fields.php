<?php


$data = json_decode(file_get_contents('dump.json'),1);

$fields = array();

foreach ($data['items'] as $item) {
		foreach ($item['metadata'] as $k =>$v) {
				$fields[$k] = '';
		}
}

foreach (array_keys($fields) as $f) {
		print $f."\n";
}
