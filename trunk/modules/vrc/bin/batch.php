#!/usr/bin/php
<?php
$days = 300;
$database = 'dase_prod';
include 'cli_setup.php';
define('APP_ROOT', 'http://quickdraw.laits.utexas.edu/dase');
define('MEDIA_ROOT', '/mnt/www-data/dase/media');

$coll = new Dase_DBO_Collection;
$coll->ascii_id = 'vrc';
if(!$coll->findOne()) {
	print "no such collection\n";
	exit;
}

$IMAGE_REPOS = "/mnt/dar/favrc/for-dase";
if (!file_exists($IMAGE_REPOS)) {
	die ("cannot find $IMAGE_REPOS");
}

/******* CREATE HASH OF IMAGES IN DAR ***********************************/
$i = 0;
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($IMAGE_REPOS));
$images = array();
foreach ($dir as $file) {
	if (!strpos($file,'/.')) {
		if (strpos($file,'.jpg') || strpos($file,'.tif')) {
			$i++;
			$images[basename($file)]= $file->getPathname();
			if (0 == $i%1000) {
				print "hashed $i files\n";
			}
			//print $file->getFilename() . "\n";
		}
	}
}

/************ CREATE HASH OF serial_number to media_file count *****************/

$media_count = array();
$db = Dase_DB::get();
$query = "
	SELECT count(m.item_id), i.serial_number
	FROM item i , media_file m
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
	ORDER BY age,acc_digital_num
	";

$sql = "
	SELECT  
	acc_digital_num, 
	acc_num_PK
	FROM tblAccession 
	WHERE acc_digital_num != ''
	";
	//WHERE acc_digital_num like '66-02608%'

$st = $pdo->prepare($sql);
$st->setFetchMode(PDO::FETCH_ASSOC);
$st->execute();
while ($row = $st->fetch()) {
//	print_r($row); exit;
	$df = $row['acc_digital_num'];
	//we'll only perform operations on items for which we have a file
	if (isset($images[$df])) {
		//skip items that already have 6 media_files
		if (isset($media_count[$row['acc_num_PK']]) && 6 == $media_count[$row['acc_num_PK']]) {
			print "{$row['acc_num_PK']} already exists and has 6 media items!\n";
		} else {
			build($row['acc_num_PK'],$coll,$media_count,$images);
		}
	}
}

function build($sernum,$coll,$media_count,$images) {
	$url = APP_ROOT . "/modules/vrc/$sernum";
	print "retrieve $url\n";
	$sxe = new SimpleXMLElement($url, NULL, TRUE);
	$item = new Dase_DBO_Item;
	$item->serial_number = $sernum;
	$item->collection_id = $coll->id;
	if (!$item->findOne()) {
		$item->item_type_id = 0;
		$item->status = 'public';
		$item->insert();
	} else {
		if (isset($media_count[$sernum])) {
			print "$item->serial_number already exists , but has {$media_count[$sernum]} items!\n";
		} else {
			print "potential problem $sernum !!!!!! (DASe has item, but we don't have image)\n";
			//return;
		}
	}

	print "\nWORKING ON $item->serial_number\n";

	//shoould be in class
	$val = new Dase_DBO_Value;
	$val->item_id = $item->id;
	foreach ($val->find() as $doomed) {
		print "deleting $doomed->value_text\n";
		$doomed->delete();
	}

	//shoould be in class
	$mf = new Dase_DBO_MediaFile;
	$mf->item_id = $item->id;
	foreach ($mf->find() as $doomed2) {
		print "deleting $doomed2->filename\n";
		$doomed2->delete();
	}

	foreach ($sxe->item[0]->metadata as $m) {
		$a = new Dase_DBO_Attribute;
		$a->collection_id = $coll->id;
		$a->ascii_id = $m['attribute_ascii_id'];
		$a->findOne();
		$v = new Dase_DBO_Value;
		$v->item_id = $item->id;
		$v->attribute_id = $a->id;
		$v->value_text = $m;
		$v->insert();
		print "inserted $a->attribute_name : $m\n";
	}

	$file = (string) $sxe->item[0]['digital_file'];
	//print "retrieving htttp://quickdraw.laits.utexas.edu/dase/modules/vrc/image/$file\n";
	//$img = file_get_contents("http://quickdraw.laits.utexas.edu/dase/modules/vrc/image/$file");
	$path = $images[$file];
	if ($path && copy($path,"/tmp/$file")) {
		print "copied $path to /tmp/$file\n";

		//file_put_contents("/tmp/$file",$img);

		makeThumbnail("/tmp/$file",$item,$coll);
		makeViewitem("/tmp/$file",$item,$coll);
		makeSizes("/tmp/$file",$item,$coll);
		unlink("/tmp/$file");

		print "building search index......";
		$item->buildSearchIndex();
		print "done.\n\n";
	} else {
		print "no go $file ($path)\n";
	}
}

function makeThumbnail($filename,$item,$coll) {
	$base = basename($filename,'.tif');
	$base = basename($base,'.jpg');
	print("--------------------------- base file name: $base ---------------------------\n");
	$results = exec("/usr/bin/mogrify -format jpeg -resize '100x100 >' -colorspace RGB $filename");
	$thumbnail = MEDIA_ROOT ."/vrc_collection/thumbnails/$item->serial_number" . '_100.jpg';  
	$mogrified_file = "/tmp/$base.jpeg";
	rename($mogrified_file,$thumbnail);
	$thumb_file_info = getimagesize($thumbnail);

	$media_file = new Dase_DBO_MediaFile;
	$media_file->item_id = $item->id;
	$media_file->filename = $item->serial_number . '_100.jpg';
	if ($thumb_file_info) {
		$media_file->width = $thumb_file_info[0];
		$media_file->height = $thumb_file_info[1];
	}
	$media_file->mime_type = 'image/jpeg';
	$media_file->size = 'thumbnail';
	$media_file->p_collection_ascii_id = $coll->ascii_id;
	$media_file->p_serial_number = $item->serial_number;
	$media_file->insert();
	print "created $media_file->filename\n";
}

function makeViewitem($filename,$item,$coll) {
	$base = basename($filename,'.tif');
	$base = basename($base,'.jpg');
	$results = exec("/usr/bin/mogrify -format jpeg -resize '400x400 >' -colorspace RGB $filename");
	$viewitem = MEDIA_ROOT ."/vrc_collection/400/$item->serial_number" . '_400.jpg';  
	$mogrified_file = "/tmp/$base.jpeg";
	rename($mogrified_file,$viewitem);
	$thumb_file_info = getimagesize($viewitem);

	$media_file = new Dase_DBO_MediaFile;
	$media_file->item_id = $item->id;
	$media_file->filename = $item->serial_number . '_400.jpg';
	if ($thumb_file_info) {
		$media_file->width = $thumb_file_info[0];
		$media_file->height = $thumb_file_info[1];
	}
	$media_file->mime_type = 'image/jpeg';
	$media_file->size = 'viewitem';
	$media_file->p_collection_ascii_id = $coll->ascii_id;
	$media_file->p_serial_number = $item->serial_number;
	$media_file->insert();
	print "created $media_file->filename\n";
}

function makeSizes($filename,$item,$coll) {
	$image_properties = array(
		'small' => array(
			'geometry'        => '640x480',
			'max_height'      => '480',
			'size_tag'        => '_640'
		),
		'medium' => array(
			'geometry'        => '800x600',
			'max_height'      => '600',
			'size_tag'        => '_800'
		),
		'large' => array(
			'geometry'        => '1024x768',
			'max_height'      => '768',
			'size_tag'        => '_1024'
		),
		'full' => array(
			'geometry'        => '3600x2700',
			'max_height'      => '2700',
			'size_tag'        => '_3600'
		),
	);
	$last_height = 0;
	$last_width = 0;
	foreach ($image_properties as $size => $size_info) {
		$base = basename($filename,'.tif');
		$base = basename($base,'.jpg');
		$results = exec("/usr/bin/mogrify -format jpeg -resize '$size_info[geometry] >' -colorspace RGB $filename");
		$mogrified_file = "/tmp/$base.jpeg";
		$newimage = MEDIA_ROOT ."/vrc_collection/$size/$item->serial_number$size_info[size_tag].jpg";  
		rename($mogrified_file,$newimage);
		$file_info = getimagesize($newimage);

		//create the media_file entry
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = "$item->serial_number$size_info[size_tag].jpg";
		if ($file_info) {
			$media_file->width = $file_info[0];
			$media_file->height = $file_info[1];
		}

		if (($media_file->width <= $last_width) && ($media_file->height <= $last_height)) {
			return;
		}

		$last_width = $media_file->width;
		$last_height = $media_file->height;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = $size;
		$media_file->p_collection_ascii_id = $coll->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		print "created $media_file->filename\n";
	}
}
