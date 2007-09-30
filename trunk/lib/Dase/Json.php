<?php
//from O'Reilly ShortCut Ajax with PHP5
class Dase_Json 
{ 
	public $json = '';

	private function encodeArray($data,$level) 
	{
		$this->json .= "[";
		foreach ( $data as $value ) { 
			Dase_Json::encodeData($value,$level+1); 
			if ( (++$i) != count($data) ) $this->json .= ",";
		}
		$this->json .= "]"; 
	} 
	private function encodeObject($data,$level) 
	{
		$this->json .= "{"; 
		$i=0; 
		foreach ( $data as $key => $value ) {
			$this->json .= "\"$key\":"; 
			Dase_Json::encodeData($value,$level+1); 
			if ( (++$i) != count($data) ) $this->json .= ",";
		}
		$this->json .= "}"; 
	} 
	public function encodeData($data,$level) 
	{
		if ( is_array($data) ) 
		{ 
			if ( is_numeric(implode(array_keys($data))) ) 
				Dase_Json::encodeArray($data,$level); 
			else 
				Dase_Json::encodeObject($data,$level); 
		} else { 
			$this->json .= "\"".$data."\"";
		} 
		return $this->json;
	} 
	public function encodeFromArray( array $data ) 
	{
		Dase_Json::encodeData($data,1);
		return $this->json;
	}
}
