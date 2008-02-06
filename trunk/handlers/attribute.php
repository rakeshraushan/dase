<?php

class AttributeHandler
{

	public static function attributeValuesAsHtml() {
		$params = Dase::instance()->params;
		$att = $params['attribute_ascii_id'];
		$coll = $params['collection_ascii_id'];
		$attr = Dase_DB_Attribute::get($coll,$att);
		if (0 == $attr->collection_id) {
			//since it is admin att we need to be able to limit to items in this coll
			$values_array = $attr->getDisplayValues($coll);
		} else {
			$values_array = $attr->getDisplayValues();
		}
		$html_coda = '</ul>';
		if (is_array($values_array) && (count($values_array) == 400)) {
			$html_coda = "<div class=\"alert\">Only the first 400 values are displayed.</div>";
		}
		$html ="<h4>Select <span class=\"attributeName\">$attr->attribute_name</span> Value:</h4>";
		$html .="<ul>";

		if (is_array($values_array)) {
			foreach ($values_array as $value) {
				$md5 = $value['value_text_md5'];
				$text = $value['value_text'];
				$tally = $value['tally'];
				if (strlen($text) > 50) {
					$text = substr_replace($text,'...',47);
				}
				$html .=<<<EOF
		<li><a href="collection/$coll/search?$coll:$attr->ascii_id=$md5" class="val_link">$text <span class="tally">($tally)</span></a></li>
EOF;
			}
		}
		$html .= $html_coda;
		Dase::display($html);
	}
}

