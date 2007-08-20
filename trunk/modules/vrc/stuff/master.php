<?php

$days_worth = $_GET['days'] ? $_GET['days'] : 3;
$pattern = $_GET['q'];
$where = '';
if ($pattern) {
	$where = "AND acc_digital_num LIKE '$pattern%'";
} 

/*
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
 */

$images = array();
include 'images.php';

echo "<html><head><title>vrc admin</title></head><body>";


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
	DATEDIFF(d,acc_modified,CURRENT_TIMESTAMP) as age 
	FROM tblAccession 
	WHERE acc_digital_num != ''
	AND DATEDIFF(d,acc_modified,CURRENT_TIMESTAMP) < $days_worth 
	$where
	ORDER BY age,acc_digital_num
	";

$display = "";
$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();
while ($row = $st->fetch()) {
	$df = $row['acc_digital_num'];
	if (isset($images[$df])) {
		$di = daseInfo($row['acc_num_PK']);
		if ($di) {
			$msg = $di;
			$link = "<a href=\"cr\">rebuild item</a>";
		} else {
			$msg = "no DASe item";
			$link = "<a href=\"cr\">create DASe item</a>";
		}
		$display .= "
			<li>
			<a href=\"xxx\">$df</a> 
			last modified {$row['age']} days ago. 
			DASE status: $msg ($link)</li>
			";
	}
}

echo "<ul>$display</ul>";
echo "</body></html>";
