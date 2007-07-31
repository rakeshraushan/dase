<?php
$attribute_id = Dase::filterGet('attribute_id');
$collection_id = Dase::filterGet('collection_id');
$att = new Dase_DB_Attribute;
if ($attribute_id) {
	$att->load($attribute_id);
	if (0 == $att->collection_id) {
		//since it is admin att we need ot be able to limit to items in this coll
		$values_array = $att->getDisplayValues(400,$collection_id);
	} else {
		$values_array = $att->getDisplayValues(400);
	}
}
$html_coda = '</ul>';
if (is_array($values_array) && (count($values_array) == 400)) {
	$html_coda = "<div class=\"alert\">Only the first 400 values are displayed.</div>";
}
$html ="<h4>Select <span class=\"attributeName\">$att->attribute_name</span> Value:</h4>";
$html .="<ul>";

if (is_array($values_array)) {
	foreach ($values_array as $value) {
		$urlenc = $value['urlencoded_value_text'];
		$text = $value['value_text'];
		$tally = $value['tally'];
		if (!strstr($value['value_text']," ") && (strlen($value['value_text']) > 50)) {
			$value['value_text'] = substr_replace($value['value_text'],'...',47);
		}
		$html .=<<<EOF
		<li><a href="index.php?action=search&query=$urlenc&collection_id=$collection_id&attribute_id=$attribute_id" class="val_link">$text <span class="tally">($tally)</span></a></li>
EOF;
	}
}
$html .= $html_coda;
header('Content-Type: text/html; charset=utf-8');
echo $html;
exit;

