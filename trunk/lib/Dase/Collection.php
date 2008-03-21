<?php

class Dase_Collection
{
	//accepts a param array OR an ascii_id
	public static function get($params_or_ascii_id) 
	{
		if (
			is_array($params_or_ascii_id) && isset($params_or_ascii_id['collection_ascii_id']) && $params_or_ascii_id['collection_ascii_id']
		) {
			$ascii_id = $params_or_ascii_id['collection_ascii_id'];
		} else if ($params_or_ascii_id && !is_array($params_or_ascii_id)) {
			$ascii_id = $params_or_ascii_id;
		} else {
			Dase_Log::put('error','no collection found (no ascii_id)');
			Dase_Error::report(500);
		}

		$coll = Dase_DBO_Collection::get($ascii_id);

		if ($coll) {
			return $coll;
		} else {
			Dase_Log::put('error',"no collection found ($ascii_id)");
			Dase_Error::report(500);
		}
	}
}

