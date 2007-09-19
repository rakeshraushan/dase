<?php
//from O'Reilly ShortCut Ajax with PHP5

class Dase_Json { 
	private static function PrintArray($data,$level) 
	{
		echo "[";
		foreach ( $data as $value ) { 
			Dase_Json::PrintData($value,$level+1); 
			if ( (++$i) != count($data) ) echo ",";
		}
		echo "]"; 
	} 
	private static function PrintObject($data,$level) 
	{
		echo "{"; 
		$i=0; 
		foreach ( $data as $key => $value ) {
			echo "\"$key\":"; 
			Dase_Json::PrintData($value,$level+1); 
			if ( (++$i) != count($data) ) echo ",";
		}
		echo "}"; 
	} 
	public static function PrintData($data,$level) 
	{
		if ( is_array($data) ) 
		{ 
			if ( is_numeric(implode(array_keys($data))) ) 
				Dase_Json::PrintArray($data,$level); 
			else 
				Dase_Json::PrintObject($data,$level); 
		} else { 
			echo "\"".$data."\"";
		} 
	} 
	public static function PrintFromArray( array $data ) 
	{
		Dase_Json::PrintData($data,1);
	}
}
