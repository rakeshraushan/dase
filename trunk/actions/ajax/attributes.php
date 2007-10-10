<?php
$attribute = new Dase_DB_Attribute;
$c = Dase_DB_Collection::get($params['collection_ascii_id']);
$attribute->collection_id = $c->id;
if ('public' == $params['pub']) {
	$attribute->is_public = 1;
}
$attribute->orderBy('sort_order');
$attribute_array = $attribute->findAll();

$html ="<h4>Select Attribute:</h4>";
$html .="<ul id=\"attList\">";

if (is_array($attribute_array)) {
	foreach ($attribute_array as $attribute) {
		$html .=<<<EOF
			<li><a href="#" class="att_link {$attribute['id']}" id="att_link_{$attribute['id']}">{$attribute['attribute_name']} <span class="tally" id="tally-{$attribute['id']}"></span></a></li>
EOF;
	}
}
$html .="</ul></div>";
$tpl = new Dase_Ajax_Template;
$tpl->setText($html);
$tpl->display();

