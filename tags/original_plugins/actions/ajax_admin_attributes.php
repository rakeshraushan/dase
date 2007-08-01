<?php
$link_class = Dase::filterGet('link_class');
$collection_id = Dase::filterGet('collection_id');
$token = Dase::filterGet('token');
$attribute = new Dase_DB_Attribute;
$attribute->collection_id = 0;
$attribute_array = $attribute->findAll();

$html = "<div class=\"adminAtts\" id=\"getTallies\">";
$html .="<h4>Select Admin Attribute:</h4>";
$html .="<ul id=\"attList\">";

if (is_array($attribute_array)) {
	foreach ($attribute_array as $attribute) {
		$html .=<<<EOF
			<li><a href="#" class="$link_class {$attribute['id']}" id="att_link_{$attribute['id']}">{$attribute['attribute_name']} <span class="tally" id="tally-{$attribute['id']}"></span></a></li>
EOF;
	}
}
$html .="</ul></div>";
header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
echo $html;
exit;
