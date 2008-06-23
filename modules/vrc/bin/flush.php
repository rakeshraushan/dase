#!/usr/bin/php
<?php
$days = 100;
$database = 'dase_prod';
include 'cli_setup.php';
define('APP_ROOT', 'http://quickdraw.laits.utexas.edu/dase');
define('MEDIA_ROOT', '/mnt/www-data/dase/media');

$coll = new Dase_DBO_Collection;
$coll->ascii_id = 'vrc';
$coll->findOne();

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
	AND DATEDIFF(d,acc_modified,CURRENT_TIMESTAMP) < 999 
	AND DATEDIFF(d,acc_modified,CURRENT_TIMESTAMP) > 100 
	";

/*
$sql = "
	SELECT  
	acc_num_PK
	FROM tblAccession 
	WHERE acc_digital_num != ''
	";
 */

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
	$item = new Dase_DBO_Item;
	$item->serial_number = $sernum;
	$item->collection_id = $coll->id;
	if (!$item->findOne()) {
		print "didn't find $sernum in DASe\n";
		return;
	} 
	print (substr($item->updated,0,10) . ' ' . substr(date(DATE_ATOM),0,10)); 
	//if (substr($item->updated,0,10) == substr(date(DATE_ATOM),0,10)) {
	//	print "item $sernum was update within the last 24 hours\n";
	if (substr($item->updated,0,9) == substr(date(DATE_ATOM),0,9)) {
		print "item $sernum was update within the last ?? days\n";
		return;
	}	

	print "\nWORKING ON $item->serial_number\n";

	$url = APP_ROOT . "/modules/vrc/$sernum";
	$sxe = new SimpleXMLElement($url, NULL, TRUE);

	$val = new Dase_DBO_Value;
	$val->item_id = $item->id;
	foreach ($val->find() as $doomed) {
		print "deleting $doomed->value_text\n";
		$doomed->delete();
	}

	foreach ($sxe->item[0]->metadata as $m) {
//	foreach ($sxe->xpath('//metadata') as $m) {
		$a = new Dase_DBO_Attribute;
		$a->collection_id = $coll->id;
		$a->ascii_id = $m['attribute_ascii_id'];
		$a->findOne();
		$v = new Dase_DBO_Value;
		$v->item_id = $item->id;
		$v->attribute_id = $a->id;
		$v->value_text = $m;
		$v->insert();
		print "inserted $a->attribute_name : $m\n";
	}

	print "building search index......";
	$item->buildSearchIndex();
	print "done.\n\n";
}

 
