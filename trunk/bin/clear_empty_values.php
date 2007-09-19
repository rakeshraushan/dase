<?php

//$database = 'dase_prod';
include 'cli_setup.php';

$db = Dase_DB::get();
$sql = "
	SELECT *
	FROM value
	WHERE value_text is null
	OR value_text ilike '   %'
	OR value_text = ''
";
$sth = $db->query($sql);
$sth->setFetchMode(PDO::FETCH_ASSOC);
$i = 0;
while  ($row = $sth->fetch()) {
	$v = new Dase_DB_Value($row);
	$i++;
	print_r($v);
	print "deleted $i values\n";
	$v->delete();
}

