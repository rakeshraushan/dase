<?php
class Dept 
{
	public static function getDept($id)
	{
		$url = "http://web.austin.utexas.edu/cola/xml/unit-heads.xml";
		$xml = file_get_contents($url);
		$xml = mb_convert_encoding($xml, "CP1252", "UTF-8");
		$dom = new DOMDocument('1.0','utf-8');
		$dom->loadXml($xml);
		foreach ($dom->getElementsByTagName('unit') as $unit) {
			if ($id == $unit->getAttribute('id')) {
				$dept['id'] = $id; 
				$dept['name'] = $unit->childNodes->item(0)->nodeValue; 
				$dept['chair'] = $unit->childNodes->item(1)->nodeValue; 
				$dept['phone'] = $unit->childNodes->item(2)->nodeValue; 
				$dept['email'] = $unit->childNodes->item(3)->nodeValue; 
				return $dept;
			}
		}
	}
}

