<?php
$coll_ascii_id = $params['collection_ascii_id'];
$attribute = new Dase_DB_Attribute;
$attribute->collection_id = 0;
$attribute_array = $attribute->findAll();

$html ="<h4 id=\"adminAttsLabel\" class=\"hide\">Select Admin Attribute:</h4>";
$html .="<div id=\"get_admin_tallies\">Loading...getting tallies...</div>";
$html .="<ul id=\"attList\" class=\"hide\">";

if (is_array($attribute_array)) {
	foreach ($attribute_array as $attribute) {
		$html .=<<<EOF
			<li><a href="html/$coll_ascii_id/attribute/{$attribute['ascii_id']}" id="{$attribute['ascii_id']}" class="att_link">{$attribute['attribute_name']} <span class="tally"></span></a></li>
EOF;
	}
}
$html .="</ul></div>";
$tpl = new Dase_Html_Template;
$tpl->setText($html);
$tpl->display();
