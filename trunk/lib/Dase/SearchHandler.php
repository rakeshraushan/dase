<?php

class Dase_SearchHandler 
{
	public static function index() {
		$query = Dase_Utils::filterGet('query');
		$refine = Dase_Utils::filterGet('refine');
		$last_refine = Dase_Utils::filterGet('last_refine');
		if ($last_refine) {
			$refine = $last_refine . " " . $refine);
		}
		$exact = Dase_Utils::filterGet('exact');
		$attribute_id = Dase_Utils::filterGet('attribute_id');
		$collection_id = Dase_Utils::filterGet('collection_id');
		$collection_array = Dase_Utils::filterGet('colls');
		$sort_by = Dase_Utils::filterGet('sort_by');
		echo $query; exit;	
		Dase_Session::set('last_search',$query);
		Dase::reload();
	}
}
