#!/usr/bin/php
<?php
$days = 400;
$database = 'dase_prod';
include 'cli_setup.php';
define('APP_ROOT', 'http://quickdraw.laits.utexas.edu/dase');
define('MEDIA_ROOT', '/mnt/www-data/dase/media');

$coll = new Dase_DB_Collection;
$coll->ascii_id = 'vrc_collection';
$coll->findOne();

$host = "SQL01.austin.utexas.edu:1036";
$name = "vrc_live";
$user = "dasevrc";
$pass = "d453vrc";

$pdo = new PDO("dblib:host=$host;dbname=$name", $user, $pass);

$sql = "
	SELECT  
	acc_num_PK
	FROM tblAccession 
	WHERE acc_digital_num != ''
	AND DATEDIFF(d,acc_modified,CURRENT_TIMESTAMP) < $days 
	";

$sql = "
	SELECT  
	acc_num_PK
	FROM tblAccession 
	WHERE acc_digital_num != ''
	";

$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();
$i = 0;
while ($row = $st->fetch()) {
	$i++;
	$mem = memory_get_usage(true);
	print "\n $i completed ($mem memory)\n";
	build($row['acc_num_PK'],$coll);
}

function build($sernum,$coll) {
	print "$sernum\n";	
	$item = new Dase_DB_Item;
	$item->serial_number = $sernum;
	$item->collection_id = $coll->id;
	if (!$item->findOne()) {
		print "didn't find $sernum in DASe\n";
		return;
	} 
	if ($item->last_update > (time()-60*60*24)) {
		print "item $sernum was update within the last 24 hours\n";
		return;
	}	

	print "\nWORKING ON $item->serial_number\n";

	$url = APP_ROOT . "/modules/vrc/$sernum";
	$sxe = new SimpleXMLElement($url, NULL, TRUE);

	$val = new Dase_DB_Value;
	$val->item_id = $item->id;
	foreach ($val->findAll() as $row) {
		$dv = new Dase_DB_Value($row);
		$dv->delete();
	}

	foreach ($sxe->item[0]->metadata as $m) {
//	foreach ($sxe->xpath('//metadata') as $m) {
		$a = new Dase_DB_Attribute;
		$a->collection_id = $coll->id;
		$a->ascii_id = $m['attribute_ascii_id'];
		$a->findOne();
		$v = new Dase_DB_Value;
		$v->item_id = $item->id;
		$v->attribute_id = $a->id;
		$v->value_text = $m;
		$v->value_text_md5 = md5($m);
		$v->insert();
		print "inserted $a->attribute_name : $m\n";
	}

	print "building search index......";
	$item->buildSearchIndex();
	print "done.\n\n";
}

 
