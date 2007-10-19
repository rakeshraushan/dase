<?php

$attribute = new Dase_DB_Attribute;
$attribute->collection_id = Dase_DB_Object::getId('collection',$params['collection_ascii_id']);
$attribute->is_public = true;
$att_array = array();
foreach($attribute->findAll() as $att) {
	$att_array[] =
		array(
			'id' => $att['id'],
			'ascii_id' => $att['ascii_id'],
			'attribute_name' => $att['attribute_name']
		);
}
$js = new Dase_Json;
$tpl = new Dase_Json_Template;
$tpl->setJson($js->encodeData($att_array));
$tpl->display();

