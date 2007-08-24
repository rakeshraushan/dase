<?php
$database = 'dase_prod';
include 'cli_setup.php';
$IMAGE_REPOS = "/mnt/dar/favrc/for-dase";
if (!file_exists($IMAGE_REPOS)) {
	die ("cannot find $IMAGE_REPOS");
}
$images = array();
$media_count = array();

$coll = Dase_DB_Collection::get('vrc_collection');

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($IMAGE_REPOS));
foreach ($dir as $file) {
	if (!strpos($file,'/.')) {
		if (strpos($file,'.jpg') || strpos($file,'.tif')) {
			$images[basename($file)] = $file->getPathname();
		}
	}
}

$db = Dase_DB::get();
$query = "
	SELECT count(m.item_id), i.serial_number
	FROM media_file m , item i
	WHERE
	m.item_id = i.id
	AND
	i.collection_id = $coll->id
	GROUP BY m.item_id, i.serial_number
	ORDER BY count DESC
	";

$sth = $db->prepare($query);
$sth->setFetchMode(PDO::FETCH_ASSOC);
$sth->execute();
while ($row = $sth->fetch()) {
	$media_count[$row['serial_number']] = $row['count'];
}

print_r($media_count);
