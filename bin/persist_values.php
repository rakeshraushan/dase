<?php

$database = 'dase_prod';
include 'cli_setup.php';


$sql = "
	SELECT c.ascii_id as collection_ascii_id,i.serial_number,a.ascii_id as attribute_ascii_id,v.id
	FROM value v, collection c, attribute a, item i
	WHERE i.id = v.item_id
	AND i.collection_id = c.id
	AND a.id = v.attribute_id
	AND a.collection_id = i.collection_id
	";
$i = 0;
foreach(Dase_DBO::query($sql) as $res) {
	$i++;
	$value = new Dase_DBO_Value;
	$value->load($res->id);
	if ($value->p_collection_ascii_id != $res->collection_ascii_id) {
		$value->p_collection_ascii_id = $res->collection_ascii_id;
		$value->p_attribute_ascii_id = $res->attribute_ascii_id;
		$value->p_serial_number = $res->serial_number;
		$value->update();
		print "updating $value->id ($i of a whole lot)\n";
	} else {
		print "skipped $value->id ($i of a whole lot)\n";
	}
	unset ($value);
}

