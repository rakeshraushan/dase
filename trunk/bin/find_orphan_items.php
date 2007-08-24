<?php
require_once 'cli_setup.php';


$db = Dase_DB::get();
$sql = "
	SELECT i.serial_number
	FROM item i LEFT JOIN collection c
	ON  i.collection_id = c.id
	WHERE c.id IS NULL
	";
$sth = $db->prepare($sql);
$sth->execute();
while ($row = $sth->fetch()) {
	print "{$row['serial_number']} is an orphan item\n";
}
