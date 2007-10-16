<?php
$attribute = new Dase_DB_Attribute;
$c = Dase::instance()->collection;
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
			<li><a href="html/$c->ascii_id/attribute/{$attribute['ascii_id']}" id="{$attribute['ascii_id']}" class="att_link">{$attribute['attribute_name']} <span class="tally"></span></a></li>
EOF;
	}
}
$html .="</ul></div>";
$tpl = new Dase_Html_Template;
$tpl->setText($html);
$tpl->display();

