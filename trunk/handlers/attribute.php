<?php

class AttributeHandler
{

	public static function attributeListAsAtom($request) 
	{
		$atts = new Dase_DBO_Attribute;
		$feed = new Dase_Atom_Feed;
		foreach ($atts->find() as $att) {
			$att->injectAtomEntryData($feed->addEntry(),$att->getCollection());
		}
		Dase::display($feed->asXml(),'application/atom+xml');
	}

	public static function attributeValuesAsHtml($request)
	{
		$att = $request->get('attribute_ascii_id');
		$coll = $request->get('collection_ascii_id');
		$attr = Dase_DBO_Attribute::get($coll,$att);
		if (0 == $attr->collection_id) {
			//since it is admin att we need to be able to limit to items in this coll
			$values_array = $attr->getDisplayValues($coll);
		} else {
			$values_array = $attr->getDisplayValues();
		}
		$html_coda = '</ul>';
		if (is_array($values_array) && (count($values_array) == 400)) {
			$html_coda = "<h3 class=\"alert\">Only the first 400 values are displayed.</h3>";
		}
		$html ="<h4>Select <span class=\"attributeName\">$attr->attribute_name</span> Value:</h4>";
		$html .="<ul>";

		if (is_array($values_array)) {
			foreach ($values_array as $value) {
				$encoded = urlencode($value['value_text']);
				$text = $value['value_text'];
				$tally = $value['tally'];
				if (strlen($text) > 50) {
					$text = substr_replace($text,'...',47);
				}
				$html .=<<<EOF
		<li><a href="collection/$coll/search?$coll.$attr->ascii_id=$encoded" class="val_link">$text <span class="tally">($tally)</span></a></li>
EOF;
			}
		}
		$html .= $html_coda;
		Dase::display($html);
	}
}

