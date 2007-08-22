<?php

$ser = Dase::filterGet('ser');
$days = Dase::filterGet('days');
$q = Dase::filterGet('q');
$where = '';
if ($q) {
	$where = "AND acc_digital_num LIKE '$q%'";
} 

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

$tpl = Dase_Template::instance('vrc');
$host = "SQL01.austin.utexas.edu:1036";
$name = "vrc_live";
$user = "dasevrc";
$pass = "d453vrc";

$pdo = new PDO("dblib:host=$host;dbname=$name", $user, $pass);
$sql = "
	SELECT  
	acc_digital_num, 
	acc_num_PK,
	acc_modified,
	DATEDIFF(d,acc_modified,CURRENT_TIMESTAMP) as age 
	FROM tblAccession 
	WHERE acc_digital_num != ''
	AND DATEDIFF(d,acc_modified,CURRENT_TIMESTAMP) < $days 
	$where
	ORDER BY age,acc_digital_num
	";

if ('none' == $days) {
	$sql = "
		SELECT  
		acc_digital_num, 
		acc_num_PK,
		acc_modified
		FROM tblAccession 
		WHERE acc_digital_num != ''
		AND acc_modified = ''
		$where
		ORDER BY acc_digital_num
		";
}

if ($ser) {
	$sql = "
		SELECT 
		acc_digital_num, 
		acc_num_PK,
		acc_modified,
		DATEDIFF(d,acc_modified,CURRENT_TIMESTAMP) as age 
		FROM tblAccession 
		WHERE acc_num_PK = '$ser'
		";
}

$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();
$items = array();
while ($row = $st->fetch()) {
	$df = $row['acc_digital_num'];
	if (isset($images[$df])) {
		if (isset($media_count[$row['acc_num_PK']])) {
			$count = $media_count[$row['acc_num_PK']];
		} else {
			$count = 0;
		}
		$items[] = "
			<li>
			<span class=\"imageFile\">$df</span> 
			modified {$row['acc_modified']}. 
			$count media files in DASe. 
			<a href=\"build/{$row['acc_num_PK']}?days=$days&q=$q\">re/build</a>
			</li>
			";
	}
	if (count($items) > 500) {
		$msg = "Maximum item limit was reached. Please refine your search";
		$tpl->assign('msg',$msg);
		break;
	}
}
$tpl->assign('items',$items);
$tpl->assign('ser',$ser);
$tpl->assign('days',$days);
$tpl->assign('q',$q);
$tpl->display('index.tpl');
