<?php
// +-- Here's the function: (from PHP.NET)
function mu_sort ($array, $key_sort) { // start function
	$key_sorta = explode(",", $key_sort);
	$keys = array_keys($array[0]);
	// sets the $key_sort vars to the first
	for($m=0; $m < count($key_sorta); $m++){ $nkeys[$m] = trim($key_sorta[$m]); }
	$n += count($key_sorta);    // counter used inside loop
	// this loop is used for gathering the rest of the
	// key's up and putting them into the $nkeys array
	for($i=0; $i < count($keys); $i++){ // start loop
		// quick check to see if key is already used.
		if(!in_array($keys[$i], $key_sorta)){
			// set the key into $nkeys array
			$nkeys[$n] = $keys[$i];
			// add 1 to the internal counter
			$n += "1";
		} // end if check
	} // end loop
	// this loop is used to group the first array [$array]
	// into it's usual clumps
	for($u=0;$u<count($array); $u++){ // start loop #1
		// set array into var, for easier access.
		$arr = $array[$u];
		// this loop is used for setting all the new keys
		// and values into the new order
		for($s=0; $s<count($nkeys); $s++){
			// set key from $nkeys into $k to be passed into multidimensional array
			$k = $nkeys[$s];
			// sets up new multidimensional array with new key ordering
			$output[$u][$k] = $array[$u][$k];
		} // end loop #2
	} // end loop #1
	// sort
	sort($output);
	// return sorted array
	return $output;
} // end function
function sortByLastUpdateSortable($b,$a) {
	if ($a->lastUpdateSortable == $b->lastUpdateSortable) {
		return 0;
	}
	return ($a->lastUpdateSortable < $b->lastUpdateSortable) ? -1 : 1;
}
function sortByItemCount($b,$a) {
	if ($a->item_count == $b->item_count) {
		return 0;
	}
	return ($a->item_count < $b->item_count) ? -1 : 1;
}
function sortBySubLevel($a,$b) {
	if ($a->sub_level == $b->sub_level) {
		return 0;
	}
	return ($a->sub_level < $b->sub_level) ? -1 : 1;
}
function sortByAttributeName($a,$b) {
	$a_str = strtolower($a->attribute_name);
	$b_str = strtolower($b->attribute_name);
	if ($a_str == $b_str) {
		return 0;
	}
	return ($a_str < $b_str) ? -1 : 1;
}
function sortBySortOrder($a,$b) {
	$a_str = $a->sort_order;
	$b_str = $b->sort_order;
	if ($a_str == $b_str) {
		return 0;
	}
	return ($a_str < $b_str) ? -1 : 1;
}
function sortByCollectionName($a,$b) {
	$a_str = strtolower($a->collection_name);
	$b_str = strtolower($b->collection_name);
	if ($a_str == $b_str) {
		return 0;
	}
	return ($a_str < $b_str) ? -1 : 1;
}
function sortByTitle($a,$b) {
	$a_str = strtolower($a->title);
	$b_str = strtolower($b->title);
	if ($a_str == $b_str) {
		return 0;
	}
	return ($a_str < $b_str) ? -1 : 1;
}
function sortByValueText($a,$b) {
	$a_str = strtolower($a->value_text);
	$b_str = strtolower($b->value_text);
	if ($a_str == $b_str) {
		return 0;
	}
	return ($a_str < $b_str) ? -1 : 1;
}
function sortValuesByAttributeSortOrder($a,$b) {
	$a_so = $a->attribute->sort_order;
	$b_so = $b->attribute->sort_order;
	if ($a_so == $b_so) {
		return 0;
	}
	return ($a_so < $b_so) ? -1 : 1;
}
function diffObjects($obj_array_a,$obj_array_b,$member_name) {
	if (is_array($obj_array_a)) {
		foreach ($obj_array_a as $object_a) {
			$object_a_member_array[] = $object_a->$member_name;
		}
	} else {
		$object_a_member_array = array();
	}
	if (is_array($obj_array_b)) {
		foreach ($obj_array_b as $object_b) {
			$object_b_member_array[] = $object_b->$member_name;
		}
	} else {
		$object_b_member_array = array();
	}
	foreach ($object_a_member_array as $member_a) {
		if (!in_array($member_a,$object_b_member_array)) {
			$in_a_not_b[] = $member_a;
			$set['in_a_not_b']['string'] = join(', ',$in_a_not_b);
			$set['in_a_not_b']['array'] = $in_a_not_b;
		}
	}
	foreach ($object_b_member_array as $member_b) {
		if (!in_array($member_b,$object_a_member_array)) {
			$in_b_not_a[] = $member_b;
			$set['in_b_not_a']['string'] = join(', ',$in_b_not_a);
			$set['in_b_not_a']['array'] = $in_b_not_a;
		}
	}
	return $set;
}
function stopApp($time_start) {
	$time_stop = microtime_float();
	$timer = round($time_stop - $time_start, 4);
	echo $timer; exit;
}
?>
