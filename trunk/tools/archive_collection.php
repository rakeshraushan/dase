<?php

include 'config.php';

$user = 'pkeane';
$pass = 'oooo88';
$coll = 'asian_studies';
$target_dir = '/mnt/home/pkeane/dase_backup_sets';

if (!file_exists($target_dir)) {
	mkdir($target_dir);
}

if (!file_exists($target_dir.'/'.$coll)) {
	mkdir($target_dir.'/'.$coll);
}

foreach (file(APP_ROOT.'/collection/'.$coll.'/items.txt') as $ln) {
	$sernum = trim($ln);
	$base_url = str_replace('http://','http://'.$user.':'.$pass.'@',APP_ROOT);
	$entry_xml = file_get_contents($base_url.'/item/'.$coll.'/'.$sernum.'.atom');
	file_put_contents($target_dir.'/'.$coll.'/'.$sernum.'.atom',$entry_xml);
	$entry = Dase_Atom_Entry::load($entry_xml);
	$enc = $entry->getEnclosure();
	$file_url = $enc['href'];
	$file_name_parts = explode('/',$file_url);
	$file_name = array_pop($file_name_parts);
	if ($file_name) {
		$file_url = str_replace('http://','http://'.$user.':'.$pass.'@',$file_url);
		file_put_contents($target_dir.'/'.$coll.'/'.$file_name,file_get_contents($file_url));
		print $sernum . "\n";
	}
}
