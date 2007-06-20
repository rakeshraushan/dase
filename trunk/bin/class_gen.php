#!/usr/local/bin/php
<?php
define("DASE_PATH",'..');
ini_set('include_path','.:../lib:/usr/local/dase/lib');
include 'Dase/DB.php';
include 'Dase/Config.php';

$class_dir = '../lib/Dase';
if (!file_exists($class_dir)) {
	mkdir ($class_dir,0755);
}
if (!file_exists($class_dir . '/DB')) {
	mkdir ($class_dir . '/DB', 0755);
}
if (!file_exists($class_dir . '/DB/Autogen')) {
	mkdir ($class_dir . '/DB/Autogen', 0755);
}

function capFirst (&$item,$key) {
	$item = ucfirst($item);
}
foreach (Dase_DB::listTables() as $table) {
	$cols = '';
	foreach (Dase_DB::listColumns($table) as $col) {
		if ('id' != $col) {
			$cols[] = "'$col'";
		}
	}
	$cols_list = implode(',',$cols);
	$parts = explode('_',$table);
	array_walk($parts,'capFirst');
	$class_root_name = implode('',$parts);
	$db_class_name = 'Dase_DB_Autogen_' . $class_root_name;
	$db_class_text = "<?php

require_once 'Dase/DB/Object.php';

class $db_class_name extends Dase_DB_Object 
{
	function __construct(\$assoc = false) {
		parent::__construct( '$table',  array($cols_list));
		if (\$assoc) {
			foreach ( \$assoc as \$key => \$value) {
				\$this->\$key = \$value;
			}
		}
	}
}";		
$db_class_filepath = $class_dir . '/DB/Autogen/' . $class_root_name . '.php';
$fh = fopen($db_class_filepath,'w');
	if (-1 == fwrite($fh,$db_class_text)) { 
		die("no go write $db_class_filepath"); 
	}
	fclose($fh) or die("no go close");
	print "created $db_class_name\n";

	$class_filepath = $class_dir . '/DB/' . $class_root_name . '.php';
	$class_name = 'Dase_DB_' . $class_root_name;
	if (!file_exists($class_filepath)) {
		$class_text = "<?php

require_once 'Dase/DB/Autogen/$class_root_name.php';

class $class_name extends $db_class_name 
{

}";		
$fh = fopen($class_filepath,'w');
if (-1 == fwrite($fh,$class_text)) { 
	die("no go write $class_filepath"); 
}
fclose($fh) or die("no go close");
print "created $class_name\n";
}
}

