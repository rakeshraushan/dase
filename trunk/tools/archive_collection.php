<?php

include 'config.php';

$coll = 'plan2';
$user = 'pkeane';
$pass = 'okthen';
$target_dir = '/mnt/home/pkeane/dase_backup_sets';

$auth = base64_encode($user.':'.$pass);
$header = array("Authorization: Basic $auth");
$opts = array( 'http' => array ('method'=>'GET','header'=>$header));
$ctx = stream_context_create($opts);

//example: file_get_contents($url,false,$ctx);

if (!file_exists($target_dir)) {
	mkdir($target_dir);
}

if (!file_exists($target_dir.'/'.$coll)) {
	mkdir($target_dir.'/'.$coll);
}

if (!file_exists($target_dir.'/'.$coll.'/media')) {
	mkdir($target_dir.'/'.$coll.'/media');
}

foreach (file(APP_ROOT.'/collection/'.$coll.'/archive.uris') as $ln) {
	$ln = trim($ln);
	if ('#' == substr($ln,0,1)) {
		$entrytype = substr($ln,1);
		$this_dir = $target_dir.'/'.$coll.'/'.$entrytype;
		if (!file_exists($this_dir)) {
			mkdir($this_dir);
		}
	} else {
		$entry_xml = file_get_contents($ln,false,$ctx);
		if ('item_type_relations' == $entrytype) {
			$parts = (explode('/',$ln));
			$filename = join('_',array_slice($parts,-3,3));
		} else {
			$filename = array_pop(explode('/',$ln));
		}
		file_put_contents($this_dir.'/'.$filename,$entry_xml);
		print "writing $this_dir -> $filename\n";
	}
	/*
	if ('items' == $entrytype) {	
		$entry = Dase_Atom_Entry::load($entry_xml);
		$enc = $entry->getEnclosure();
		$file_url = $enc['href'];
		$file_name_parts = explode('/',$file_url);
		$file_name = array_pop($file_name_parts);
		if ($file_name) {
			file_put_contents(
				$target_dir.'/'.$coll.'/media/'.$file_name,
				file_get_contents($file_url,false,$ctx)
			);
			print "writing media file $file_name\n";
		}
	}
	 */
}
