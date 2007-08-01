<?php
$query = Dase::filterGet('query');
$refine = Dase::filterGet('refine');
$last_refine = Dase::filterGet('last_refine');
if ($last_refine) {
	$refine = $last_refine . " " . $refine;
}
$exact = Dase::filterGet('exact');
$attribute_id = Dase::filterGet('attribute_id');
$collection_id = Dase::filterGet('collection_id');
$collection_array = Dase::filterGet('colls');
$sort_by = Dase::filterGet('sort_by');
print_r(Dase_Search::find($query)); exit;
Dase_Session::set('last_search',$query);
Dase::reload();
