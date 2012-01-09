<?php

function search($att,$val) {
		$data_file = $att.'_index.json';
		$all_data = json_decode(file_get_contents($data_file),1);
		$index = $all_data['lookup_list'][$att];
		$items = array();
		if (isset($index[$val])) {
				foreach ($index[$val] as $item_uniq) {
						$items[] = $all_data['items'][$item_uniq];
				}
		}
		return $items;
}


print_r(search('cat_number',33));
