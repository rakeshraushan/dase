<?php

$days = $_GET['days'] ? $_GET['days'] : 3;
$pattern = trim($_GET['q']);
$where = '';
if ($pattern) {
	$where = "AND acc_digital_num LIKE '$pattern%'";
} 

$IMAGE_REPOS = "/mnt/dar/favrc/for-dase";
if (!file_exists($IMAGE_REPOS)) {
	die ("cannot find $IMAGE_REPOS");
}
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($IMAGE_REPOS));
$images = array();
foreach ($dir as $file) {
	if (!strpos($file,'/.')) {
		if (strpos($file,'.jpg') || strpos($file,'.tif')) {
			$images[basename($file)]= $file->getPathname();
		}
	}
}

$tpl = Dase_Template::instance('vrc');

//$images = array();
//include 'images.php';

function daseInfo($ser_num) {
	$url = "http://dase.laits.utexas.edu/xml/vrc_collection/$ser_num";
	$sxe = new SimpleXMLElement($url, NULL, TRUE);
	if ("no such item" == $sxe) {
		return false;
	} else {
		return count($sxe->item->media_file) . " media files in DASe";
	}
}


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

$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();
$items = array();
while ($row = $st->fetch()) {
	$df = $row['acc_digital_num'];
	if (isset($images[$df])) {
		$di = daseInfo($row['acc_num_PK']);
		if ($di) {
			$msg = "$di (<a href=\"http://dase.laits.utexas.edu/vrc_collection/{$row['acc_num_PK']}\">VIEW</a>) ";
			$link = "<a href=\"rebuild/{$row['acc_num_PK']}?days=$days&pattern=$pattern\">rebuild item</a>";
		} else {
			$msg = "no DASe item";
			$link = "<a href=\"build/{$row['acc_num_PK']}?days=$days&pattern=$pattern\">create DASe item</a>";
		}
		$items[] = "
			<li>
			<span class=\"imageFile\">$df</span> 
			modified {$row['acc_modified']}. 
			DASe status: $msg ($link)</li>
			";
	}
	if (count($items) > 100) {
		$msg = "Maximum item limit was reached. Please refine your search";
		$tpl->assign('msg',$msg);
		break;
	}
}
$tpl->assign('items',$items);
$tpl->assign('days',$days);
$tpl->assign('pattern',$pattern);
$tpl->display('index.tpl');
