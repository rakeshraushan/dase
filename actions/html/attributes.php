<?php


//this script should consume an atom feed of attributes for the collection
//and use xslt to transform it into this shape
// --maybe :-)

$attribute = new Dase_DB_Attribute;
$c = Dase_Collection::get($params['collection_ascii_id']);
$atts->collection_id = $c->id;
$atts->is_public = 1;
$atts->orderBy('sort_order');

$html ="<h4>Select Attribute:</h4>";
$html .="<div id=\"get_public_tallies\" class=\"hide\"></div>";
$html .="<ul id=\"attList\">";

foreach ($atts->find() as $attribute) {
	$html .=<<<EOF
			<li><a href="html/$c->ascii_id/attribute/$attribute->ascii_id" id="$attribute->ascii_id" class="att_link">$attribute->attribute_name <span class="tally" id="tally-{$attribute->ascii_id}"></span></a></li>
EOF;
}
$html .="</ul></div>";
Dase::display($html);

