<?php
$c = Dase_DB_Collection::get($params['collection_ascii_id']);
$att = new Dase_DB_Attribute;
$att->ascii_id = $params['attribute_ascii_id'];
$att->collection_id = $c->id;
if ($att->findOne()) {
	$df = new Dase_DB_DefinedValue;
	$df->attribute_id = $att->id;
	Dase::display(Dase_Util::simplexml_append($att->asSimpleXml(),$df->findAsXml(false))->asXml());
}

