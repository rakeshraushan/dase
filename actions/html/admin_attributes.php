<?php
$coll_ascii_id = $params['collection_ascii_id'];
$atts = new Dase_DB_Attribute;
$atts->collection_id = 0;

$html ="<h4 id=\"adminAttsLabel\" class=\"hide\">Select Admin Attribute:</h4>";
$html .="<div id=\"get_admin_tallies\">Loading...getting tallies...</div>";
$html .="<ul id=\"attList\" class=\"hide\">";

foreach ($atts->find() as $attribute) {
	$html .=<<<EOF
			<li><a href="html/$coll_ascii_id/attribute/$attribute->ascii_id" id="$attribute->ascii_id" class="att_link">$attribute->attribute_name <span class="tally" id="tally-{$attribute->ascii_id}"></span></a></li>
EOF;
}
$html .="</ul></div>";
Dase::display($html);
