<?php
include 'cli_setup.php';

$db = Dase_DB::get();
$sql = "SELECT name FROM sqlite_master
	WHERE type='table'
	ORDER BY name";
$st = $db->query($sql);
while (list($table) = $st->fetch()) {
	listCols($table,$db);
}

function listCols($table,$db) {
	$sql = "PRAGMA table_info($table)";
	$st = $db->query($sql);
	while ($row = $st->fetch()) {
		$name = $row['name'];
		$type = $row['type'];
	//print_r($row) . "\n";
		print "$table : $name : $type\n";
	}
}


