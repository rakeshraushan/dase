<?php
ini_set('include_path',ini_get('include_path').':./../lib:'); 
define('DASE_PATH','..');

include 'Dase/DB.php';
include 'Dase/DB/Attribute.php';
include 'Dase/Log.php';



$db = Dase_DB::get();
foreach( array('collection','attribute','media_file','item','value') as $table) {
$db->query("DELETE from $table");
}

