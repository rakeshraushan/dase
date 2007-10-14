<?php
$attribute = new Dase_DB_Attribute;
$attribute->collection_id = 0;
$attribute_array = $attribute->findAll();

$html ="<h4>Select Admin Attribute:</h4>";
$html .="<div id=\"get_admin_tallies\" class=\"hide\"></div>";
$html .="<ul id=\"attList\">";

if (is_array($attribute_array)) {
	foreach ($attribute_array as $attribute) {
		$html .=<<<EOF
			<li><a href="#" class="{$attribute['id']}" id="att_link_{$attribute['id']}">{$attribute['attribute_name']} <span class="tally" id="tally-{$attribute['ascii_id']}"></span></a></li>
EOF;
	}
}
$html .="</ul></div>";
$tpl = new Dase_Ajax_Template;
$tpl->setText($html);
$tpl->display();
