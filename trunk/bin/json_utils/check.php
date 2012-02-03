<?php


$data = json_decode(file_get_contents('dump.json'),1);

$fields = array();

foreach ($data['items'] as $item) {
		foreach ($item['metadata'] as $k =>$v) {
				if ($k == 'created_in') {
						print_r( $item );
				}
		}
}
