#!/usr/bin/php
<?php
include 'cli_setup.php';

$result = array();

$types['bigint'] = 'INTEGER';
$types['boolean'] = 'INTEGER';
$types['character varying'] = "TEXT";
$types['double precision'] = "REAL";
$types['double'] = "REAL";
$types['integer'] = 'INTEGER';
$types['int'] = 'INTEGER';
$types['mediumtext'] = "TEXT";
$types['text'] = "TEXT";
$types['tinyint'] = 'INTEGER';
$types['varchar'] = "TEXT";

$sx = simplexml_load_file('schema.xml');

$db = Dase_DB::get();

if ('sqlite' == Dase_DB::getDbType()) {
	foreach ($sx->table as $table) {
		foreach ($table->column as $col) {
			$col_type = (string) $col['type'];
			if ('true' == $col['is_primary_key']) {
				$col_meta = $col['name'] . " " . $types[$col_type] . " PRIMARY KEY\n";  
			} else {
				$col_meta = $col['name'] . " " . $types[$col_type];  
			}
			$cols[] = trim($col_meta); 
		}
		$col_statement = trim(join(',',$cols));
		unset($cols);
		$sql = "CREATE TABLE {$table['name']} ( $col_statement )\n\n";
		$db->exec($sql);
		print "created {$table['name']}\n";
	}
} else {
	print "sorry, this script is for sqlite ONLY\n";
}
