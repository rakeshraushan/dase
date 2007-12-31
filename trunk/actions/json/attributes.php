<?php
$attribute = new Dase_DB_Attribute;
$attribute->collection_id = Dase_DB_Collection::getId($params['collection_ascii_id']);
$attribute->is_public = true;
$attribute->orderBy('sort_order');
$att_array = array();
foreach($attribute->find() as $att) {
	$att_array[] =
		array(
			'id' => $att->id,
			'ascii_id' => $att->ascii_id,
			'attribute_name' => $att->attribute_name,
			'collection' => $params['collection_ascii_id']
		);
}
Dase::display(Dase_Json::get($att_array));
