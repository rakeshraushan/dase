<?php
$database = 'dase_prod';
require_once 'cli_setup.php';

$db = Dase_DB::get();
$sql = "select id, date_part('epoch',(date_trunc('minute',timestamp))) from value_revision_history where unix_timestamp is null";
$st = $db->query($sql);
$i = 0;
while ($row = $st->fetch()) {
	$i++;
	$vrh = new Dase_DB_ValueRevisionHistory;
	$vrh->load($row['id']);
	$vrh->unix_timestamp = $row['date_part'];
	$vrh->update();
	print "updated $i\n";
}
