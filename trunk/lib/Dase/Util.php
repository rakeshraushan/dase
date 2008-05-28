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

Class Dase_Util 
{
	function __construct() {}

	public static function getVersion()
	{
		$ver = explode( '.', PHP_VERSION );
		return $ver[0] . $ver[1] . $ver[2];
	}

	public static function dirify($str)
	{
		$str = strtolower(preg_replace('/[^a-zA-Z0-9_]/','_',$str));
		return preg_replace('/__*/','_',$str);
	}

	public static function sortByLastUpdateSortable($b,$a)
	{
		if ($a->lastUpdateSortable == $b->lastUpdateSortable) {
			return 0;
		}
		return ($a->lastUpdateSortable < $b->lastUpdateSortable) ? -1 : 1;
	}
	public static function sortByCount($b,$a)
	{
		if (count($a) == count($b)) {
			return 0;
		}
		return (count($a) < count($b)) ? -1 : 1;
	}
	public static function sortByItemCount($b,$a)
	{
		if ($a->item_count == $b->item_count) {
			return 0;
		}
		return ($a->item_count < $b->item_count) ? -1 : 1;
	}
	public static function sortBySubLevel($a,$b)
	{
		if ($a->sub_level == $b->sub_level) {
			return 0;
		}
		return ($a->sub_level < $b->sub_level) ? -1 : 1;
	}
	public static function sortByAttributeName($a,$b)
	{
		$a_str = strtolower($a->attribute_name);
		$b_str = strtolower($b->attribute_name);
		if ($a_str == $b_str) {
			return 0;
		}
		return ($a_str < $b_str) ? -1 : 1;
	}
	public static function sortBySortOrder($a,$b)
	{
		$a_str = $a->sort_order;
		$b_str = $b->sort_order;
		if ($a_str == $b_str) {
			return 0;
		}
		return ($a_str < $b_str) ? -1 : 1;
	}
	public static function sortByCollectionName($a,$b)
	{
		$a_str = strtolower($a->collection_name);
		$b_str = strtolower($b->collection_name);
		if ($a_str == $b_str) {
			return 0;
		}
		return ($a_str < $b_str) ? -1 : 1;
	}
	public static function sortByTitle($a,$b)
	{
		$a_str = strtolower($a->title);
		$b_str = strtolower($b->title);
		if ($a_str == $b_str) {
			return 0;
		}
		return ($a_str < $b_str) ? -1 : 1;
	}
	public static function sortByValueText($a,$b)
	{
		$a_str = strtolower($a->value_text);
		$b_str = strtolower($b->value_text);
		if ($a_str == $b_str) {
			return 0;
		}
		return ($a_str < $b_str) ? -1 : 1;
	}
	public static function sortValuesByAttributeSortOrder($a,$b)
	{
		$a_so = $a->attribute->sort_order;
		$b_so = $b->attribute->sort_order;
		if ($a_so == $b_so) {
			return 0;
		}
		return ($a_so < $b_so) ? -1 : 1;
	}
	public static function diffObjects($obj_array_a,$obj_array_b,$member_name)
	{
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
	public static function stopApp($time_start)
	{
		$time_stop = microtime_float();
		$timer = round($time_stop - $time_start, 4);
		echo $timer; exit;
	}
}

