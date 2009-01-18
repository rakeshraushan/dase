<?php

class Dase_Json 
{ 
	public static function get($data,$format=true)
	{
		$js = new Services_JSON;
		if ($format) {
			return $js->json_format($data);
		} else {
			return $js->encode($data);
		}
	}

	public static function toPhp($json)
	{
		$js = new Services_JSON;
		return $js->decode($json);
	}

}

