#!/usr/bin/php
<?php
//$database = 'dase_prod';
include 'cli_setup.php';
define('APP_ROOT', 'http://quickdraw.laits.utexas.edu/dase');
define('MEDIA_ROOT', '/mnt/www-data/dase/media');

$coll = new Dase_DB_Collection;
$coll->ascii_id = 'efossils_collection';
$coll->findOne();

$coll->createNewItem();

$IMAGE_REPOS = "/mnt/projects/efossils/pkeane";
if (!file_exists($IMAGE_REPOS)) {
	die ("cannot find $IMAGE_REPOS");
}

/******* CREATE HASH OF IMAGES IN REPOS ***********************************/

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($IMAGE_REPOS));
$images = array();
foreach ($dir as $file) {
	if (!strpos($file,'/.')) {
		if (strpos($file,'.jpg') || strpos($file,'.tif')) {
			$path = $file->getPathname();
			$basename = basename($file);
			print_r(getAdminMetadata($path));
		}
	}
}

exit;

$admin_attributes = array( 
	admin_audio_bitrate => 'Bitrate (kbps)',
	admin_audio_channel_mode => 'Channel Mode',
	admin_audio_sampling_rate => 'Sampling Rate (kHz)',
	admin_audio_time => 'Audio Time',
	admin_corrected_image_upload_date => 'Corrected Image Upload Date',
	admin_image_height => 'Original Image Height',
	admin_image_width => 'Original Image Width',
	admin_last_modified_date_time => 'Last Modified Date/Time',
	admin_checksum => 'Original File Checksum',
	admin_file_size => 'Original File Size',
	admin_filename => 'Original Filename',
	admin_mime_type => 'Original File Mime Type',
	admin_project_name => 'Project Name',
	admin_serial_number => 'Serial Number',
	admin_upload_date_time => 'Upload Date/Time',
	admin_upload_ip_address => 'Upload IP Address',
	admin_upload_eid => 'Upload User EID',
);

function getAdminMetadata($path) {
	$iptc['2#005'] = 'admin_iptc_object_name';
	$iptc['2#015'] = 'admin_iptc_category';
	$iptc['2#020'] = 'admin_iptc_supplemental_category';
	$iptc['2#025'] = 'admin_iptc_keywords';
	$iptc['2#055'] = 'admin_iptc_date_created';
	$iptc['2#060'] = 'admin_iptc_time_created';
	$iptc['2#062'] = 'admin_iptc_digital_creation_date';
	$iptc['2#063'] = 'admin_iptc_digital_creation_time';
	$iptc['2#065'] = 'admin_iptc_originating_program';
	$iptc['2#070'] = 'admin_iptc_program_version';
	$iptc['2#080'] = 'admin_iptc_by_line';
	$iptc['2#085'] = 'admin_iptc_by_line_title';
	$iptc['2#090'] = 'admin_iptc_city';
	$iptc['2#092'] = 'admin_iptc_sub_location';
	$iptc['2#095'] = 'admin_iptc_province_state';
	$iptc['2#100'] = 'admin_iptc_country_primary_location_code';
	$iptc['2#101'] = 'admin_iptc_country_primary_location_name';
	$iptc['2#105'] = 'admin_iptc_headline';
	$iptc['2#110'] = 'admin_iptc_credit';
	$iptc['2#115'] = 'admin_iptc_source';
	$iptc['2#116'] = 'admin_iptc_copyright_notice';
	$iptc['2#118'] = 'admin_iptc_contact';
	$iptc['2#120'] = 'admin_iptc_caption_abstract';
	$iptc['2#122'] = 'admin_iptc_caption_writer';
	$iptc['2#131'] = 'admin_iptc_image_orientation';
	//generate admin metadata
	$file_info = getimagesize($path);
	$data_hash['admin_checksum'] = md5($path);
	$data_hash['admin_filename'] = $path;
	$data_hash['admin_mime_type'] = $file_info['mime'];
	$data_hash['admin_file_size'] = filesize($path);
	if ($file_info[0]) {
		$data_hash['admin_image_width'] = $file_info[0];
	}
	if ($file_info[1]) {
		$data_hash['admin_image_height'] = $file_info[1];
	}
	require_once 'Image/IPTC.php';
	$ip = new Image_IPTC($path);
	$tags_array = $ip->getAllTags();

	if (is_array($tags_array)) {
		foreach ($ip->getAllTags() as $code => $values_array) {
			foreach ($values_array as $val) {
				$data_hash[$iptc[$code]][] = $val;
			}
		} 
	}
	$exif = exif_read_data($path);
	if (isset($exif['DateTime'])) {
		$data_hash['admin_exif_datetime'] = $exif['DateTime']; 
	}
	$data_hash['admin_upload_date_time'] = date('r');
	//$data_hash['admin_serial_number'] = $serial_number;
	return $data_hash;
}

function build($filepath,$coll) {
	$item = new Dase_DB_Item;
	$item->serial_number = $sernum;
	$item->collection_id = $coll->id;
	if (!$item->findOne()) {
		$item->item_type_id = 0;
		$item->status_id = 0;
		$item->insert();
	} else {
		if (isset($media_count[$sernum])) {
			print "$item->serial_number already exists , but has {$media_count[$sernum]} items!\n";
		} else {
			print "problem!!!!!! (DASe has item, but we don't have image)\n";
			return;
		}
	}

	print "\nWORKING ON $item->serial_number\n";

	//shoould be in class
	$val = new Dase_DB_Value;
	$val->item_id = $item->id;
	foreach ($val->findAll() as $row) {
		$dv = new Dase_DB_Value($row);
		$dv->delete();
	}

	//shoould be in class
	$mf = new Dase_DB_MediaFile;
	$mf->item_id = $item->id;
	foreach ($mf->findAll() as $row) {
		$m = new Dase_DB_MediaFile($row);
		$m->delete();
	}

	foreach ($sxe->item[0]->metadata as $m) {
		$a = new Dase_DB_Attribute;
		$a->collection_id = $coll->id;
		$a->ascii_id = $m['attribute_ascii_id'];
		$a->findOne();
		$v = new Dase_DB_Value;
		$v->item_id = $item->id;
		$v->attribute_id = $a->id;
		$v->value_text = $m;
		$v->value_text_md5 = md5($m);
		$v->insert();
		print "inserted $a->attribute_name : $m\n";
	}

	makeThumbnail($path,$item,$coll);
	makeViewitem($path,$item,$coll);
	makeSizes($path,$item,$coll);

	print "building search index......";
	$item->buildSearchIndex();
	print "done.\n\n";
}

function makeThumbnail($filename,$item,$coll) {
	$base = basename($filename,'.tif');
	$results = exec("/usr/bin/mogrify -format jpeg -resize '100x100 >' -colorspace RGB $filename");
	$thumbnail = MEDIA_ROOT ."/vrc_collection/thumbnails/$item->serial_number" . '_100.jpg';  
	$mogrified_file = "/tmp/$base.jpeg";
	rename($mogrified_file,$thumbnail);
	$thumb_file_info = getimagesize($thumbnail);

	$media_file = new Dase_DB_MediaFile;
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
	$results = exec("/usr/bin/mogrify -format jpeg -resize '400x400 >' -colorspace RGB $filename");
	$viewitem = MEDIA_ROOT ."/vrc_collection/400/$item->serial_number" . '_400.jpg';  
	$mogrified_file = "/tmp/$base.jpeg";
	rename($mogrified_file,$viewitem);
	$thumb_file_info = getimagesize($viewitem);

	$media_file = new Dase_DB_MediaFile;
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
		$results = exec("/usr/bin/mogrify -format jpeg -resize '$size_info[geometry] >' -colorspace RGB $filename");
		$mogrified_file = "/tmp/$base.jpeg";
		$newimage = MEDIA_ROOT ."/vrc_collection/$size/$item->serial_number$size_info[size_tag].jpg";  
		rename($mogrified_file,$newimage);
		$file_info = getimagesize($newimage);

		//create the media_file entry
		$media_file = new Dase_DB_MediaFile;
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
