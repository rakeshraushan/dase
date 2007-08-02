<?php

include 'cli_setup.php';

$warning =<<<EOD

THIS IS DANGEROUS
MAKE SURE YOU MEAN TO COMPLETELY
DESTROY THE DATABASE!!!!!!!!!!!!!!!


EOD;

echo $warning; exit;

$db = Dase_DB::get();
foreach( array('collection','attribute','media_file','item','value') as $table) {
//$db->query("DELETE from $table");
}

