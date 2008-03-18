<?php

//adapted from O'Reilly ShortCut Ajax with PHP5

class Dase_Json 
{ 
	public $json = '';

	public static function get($data)
	{
		$js = new Dase_Json;
		$json = $js->encodeData($data);
		//note: for better xss security, do not return arrays
		//rather make sure an array gets wrapped in an object
		$jsonObj = "{\"json\":$json}";
		return $jsonObj;
	}

	private function encodeArray($data)
	{
		$this->json .= "[";
		$i=0; 
		foreach ( $data as $value ) { 
			Dase_Json::encodeData($value); 
			if ( (++$i) != count($data) ) $this->json .= ",";
		}
		$this->json .= "]"; 
	} 
	private function encodeObject($data)
	{
		$this->json .= "{"; 
		$i=0; 
		foreach ( $data as $key => $value ) {
			$this->json .= "\"$key\":"; 
			Dase_Json::encodeData($value); 
			if ( (++$i) != count($data) ) $this->json .= ",";
		}
		$this->json .= "}"; 
	} 
	public function encodeData($data)
	{
		if ( is_array($data) ) { 
			if (is_numeric(array_shift(array_keys($data)))) 
				Dase_Json::encodeArray($data); 
			else 
				Dase_Json::encodeObject($data); 
		} else { 
			$this->json .= "\"".$data."\"";
		} 
		return $this->json;
	} 
}
