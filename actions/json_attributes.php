<?php

$attribute = new Dase_DB_Attribute;
$attribute->collection_id = Dase_DB_Object::getId('collection',$params['collection_ascii_id']);
$attribute->is_public = true;
foreach($attribute->findAll() as $att) {
	$json_obj_array[] = "{'att':
		'id':'{$att['id']}',
		'ascii_id':'{$att['ascii_id']}',
		'attribute_name':'{$att['attribute_name']}'}";
}
$json = "{'attributes':[";
$json .= join(',',$json_obj_array);
$json .= "]}";

header('Content-Type: application/json');
print $json;
