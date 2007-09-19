<?php
//from O'Reilly ShortCut Ajax with PHP5
class Dase_Json 
{ 
	private static function printArray($data,$level) 
	{
		echo "[";
		foreach ( $data as $value ) { 
			Dase_Json::printData($value,$level+1); 
			if ( (++$i) != count($data) ) echo ",";
		}
		echo "]"; 
	} 
	private static function printObject($data,$level) 
	{
		echo "{"; 
		$i=0; 
		foreach ( $data as $key => $value ) {
			echo "\"$key\":"; 
			Dase_Json::printData($value,$level+1); 
			if ( (++$i) != count($data) ) echo ",";
		}
		echo "}"; 
	} 
	public static function printData($data,$level) 
	{
		if ( is_array($data) ) 
		{ 
			if ( is_numeric(implode(array_keys($data))) ) 
				Dase_Json::printArray($data,$level); 
			else 
				Dase_Json::printObject($data,$level); 
		} else { 
			echo "\"".$data."\"";
		} 
	} 
	public static function printFromArray( array $data ) 
	{
		Dase_Json::printData($data,1);
	}
}
