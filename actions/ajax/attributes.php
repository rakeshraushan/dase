<?php
$attribute = new Dase_DB_Attribute;
$c = Dase_DB_Collection::get($params['collection_ascii_id']);
$attribute->collection_id = $c->id;
$attribute->is_public = 1;
$attribute->orderBy('sort_order');
$attribute_array = $attribute->findAll();

$html ="<h4>Select Attribute:</h4>";
$html .="<div id=\"get_public_tallies\" class=\"hide\"></div>";
$html .="<ul id=\"attList\">";

if (is_array($attribute_array)) {
	foreach ($attribute_array as $attribute) {
		$html .=<<<EOF
			<li><a href="#" class="att_link {$attribute['id']}" id="att_link_{$attribute['id']}">{$attribute['attribute_name']}</a> <span class="tally" id="tally-{$attribute['ascii_id']}"></span></li>
EOF;
	}
}
$html .="</ul></div>";
$tpl = new Dase_Ajax_Template;
$tpl->setText($html);
$tpl->display();

