<?php

class Dase_File_Audio extends Dase_File
{

	public function __construct($file) {
		$this->_start = self::microtime_float();
	}

	static function getUploadedFile($file) {
		$original_name = trim($file['name']); //orig filename
		$tmp_path = $file['tmp_name']; //temp path
		$upload_path = $file['tmp_name']; //temp path
		$mime_type = $file['type']; //mime_type
		$file_size = $file['size']; //size
		$error = $file['error']; //size
		$file_info = getimagesize($this->tmp_path);
		$types_map = array(
			'image/jpeg' => array('size' => 'jpeg', 'ext' => '.jpg'),
			'image/gif' => array('size' => 'gif', 'ext' => '.gif'),
			'image/png' => array('size' => 'png', 'ext' => '.png'),
			'image/tiff' => array('size' => 'tiff', 'ext' => '.tiff'),
			'audio/mpeg' => array('size' => 'mp3', 'ext' => '.mp3'),
			'audio/mpg' => array('size' => 'mp3', 'ext' => '.mp3'),
			'video/quicktime' => array('size' => 'quicktime', 'ext' => '.mov'),
			'application/pdf' => array('size' => 'pdf', 'ext' => '.pdf'),
			'application/xml' => array('size' => 'xml', 'ext' => '.xml'),
			'text/xml' => array('size' => 'xml', 'ext' => '.xml'),
			'application/xml+xslt' => array('size' => 'xslt', 'ext' => '.xsl'),
			'application/msword' => array('size' => 'doc', 'ext' => '.doc'),
			'text/css' => array('size' => 'css', 'ext' => '.css'),
			'text/html' => array('size' => 'html', 'ext' => '.html'),
		);
		$valid_types = array_keys($types_map);
		$size = $types_map[$this->mime_type]['size'];
		$ext = $types_map[$this->mime_type]['ext'];
	}

	function validate() {
		if ((!in_array($this->mime_type,$this->valid_types)) || (!is_uploaded_file($this->tmp_path))) {
			$msg = "Sorry, but $this->original_name is not a valid file type.";
			$msg = urlencode($msg);
			if ($this->tmp_path) {
				unlink($this->tmp_path);
			}
			return $msg;
		}
		if (!$this->file_size) {
			$msg = "Sorry, but $this->original_name seems to be an empty file";
			$msg = urlencode($msg);
			if ($this->tmp_path) {
				unlink($this->tmp_path);
			}
			return $msg;
		}
		if ('image' == substr($this->mime_type,0,5)) {
			if (!$this->file_info) {
				$msg = "Sorry, but $this->original_name is not a valid image file";
				$msg = urlencode($msg);
				if ($this->tmp_path) {
					unlink($this->tmp_path);
				}
				return $msg;
			}
		}
		return;
	}

	function stuff() {
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
	}


	function output_iptc_data( $image_path ) {   
		$size = getimagesize ( $image_path, $info);       
		if(is_array($info)) {   
			$iptc = iptcparse($info["APP13"]);
			foreach (array_keys($iptc) as $s) {             
				$c = count ($iptc[$s]);
				for ($i=0; $i <$c; $i++)
				{
					echo $s.' = '.$iptc[$s][$i].'<br>';
				}
			}                 
		}            
	}



	function getAdminMetadata() {
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
		$data_hash['admin_checksum'] = md5_file($this->tmp_path);
		$data_hash['admin_filename'] = $this->original_name;
		$data_hash['admin_mime_type'] = $this->mime_type;
		$data_hash['admin_file_size'] = $this->file_size;
		if ($this->file_info[0]) {
			$data_hash['admin_image_width'] = $this->file_info[0];
		}
		if ($this->file_info[1]) {
			$data_hash['admin_image_height'] = $this->file_info[1];
		}
		require_once 'Image/IPTC.php';
		$ip = new Image_IPTC($this->tmp_path);
		foreach ($ip->getAllTags() as $code => $values_array) {
			foreach ($values_array as $val) {
				$data_hash[$iptc[$code]][] = $val;
			}
		} 
		$exif = exif_read_data($this->tmp_path);
		if (isset($exif['DateTime'])) {
			$data_hash['admin_exif_datetime'] = $exif['DateTime']; 
		}
		return $data_hash;
	}

	function makeThumbnail($media_path,$item_id,$make_new = 0) {
		require_once 'DataObjects/Media_file.php';
		if ($make_new) {
			$this->tmp_path = "/tmp/$this->serial_number";
			$results = exec("convert xc:#ffffff -resize 80X40! -gravity 'Center' -fill '#b2170f' -draw 'text 0,0 \"$this->serial_number\"' $this->tmp_path.jpeg");
		} else {
			$results = exec("/usr/bin/mogrify -format jpeg -resize '100x100 >' -colorspace RGB $this->tmp_path");
		}
		$mogrified_file = "$this->tmp_path" . ".jpeg";
		$thumbnail = "$media_path/thumbnails/$this->serial_number" . '_100.jpg';  
		rename($mogrified_file,$thumbnail);
		$thumb_file_info = getimagesize($thumbnail);

		//create the media_file entry
		$media_file = new DataObjects_Media_file;
		$media_file->item_id = $item_id;
		$media_file->filename = $this->serial_number . '_100.jpg';
		if ($thumb_file_info) {
			$media_file->width = $thumb_file_info[0];
			$media_file->height = $thumb_file_info[1];
		}
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'thumbnail';
		$media_file->p_collection_ascii_id = $this->collection_ascii_id;
		$media_file->p_serial_number = $this->serial_number;
		$media_file->insert();
	}

	function makeViewitem($media_path,$item_id,$make_new = 0) {
		require_once 'DataObjects/Media_file.php';
		if ($make_new) {
			$this->tmp_path = "/tmp/$this->serial_number";
			$results = exec("convert xc:#ffffff -resize 80X40! -gravity 'Center' -fill '#b2170f' -draw 'text 0,0 \"$this->serial_number\"' $this->tmp_path.jpeg");
		} else {
			$results = exec("/usr/bin/mogrify -format jpeg -resize '400x400 >' -colorspace RGB $this->tmp_path");
		}
		$mogrified_file = "$this->tmp_path" . ".jpeg";
		$viewitem = "$media_path/400/$this->serial_number" . '_400.jpg';  
		rename($mogrified_file,$viewitem);
		$file_info = getimagesize($viewitem);

		//create the media_file entry
		$media_file = new DataObjects_Media_file;
		$media_file->item_id = $item_id;
		$media_file->filename = $this->serial_number . '_400.jpg';
		if ($file_info) {
			$media_file->width = $file_info[0];
			$media_file->height = $file_info[1];
		}
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'viewitem';
		$media_file->p_collection_ascii_id = $this->collection_ascii_id;
		$media_file->p_serial_number = $this->serial_number;
		$media_file->insert();
	}

	function makeSizes($media_path,$item_id) {
		require_once 'DataObjects/Media_file.php';
		$image_properties = array(
			small => array(
				geometry        => '640x480',
				max_height      => '480',
				size_tag        => '_640'
			),
			medium => array(
				geometry        => '800x600',
				max_height      => '600',
				size_tag        => '_800'
			),
			large => array(
				geometry        => '1024x768',
				max_height      => '768',
				size_tag        => '_1024'
			),
			full => array(
				geometry        => '3600x2700',
				max_height      => '2700',
				size_tag        => '_3600'
			),
		);
		foreach ($image_properties as $size => $size_info) {
			$results = exec("/usr/bin/mogrify -format jpeg -resize '$size_info[geometry] >' -colorspace RGB $this->tmp_path");
			$mogrified_file = "$this->tmp_path" . ".jpeg";
			$newimage = "$media_path/$size/$this->serial_number$size_info[size_tag].jpg";  
			rename($mogrified_file,$newimage);
			$file_info = getimagesize($newimage);

			//create the media_file entry
			$media_file = new DataObjects_Media_file;
			$media_file->item_id = $item_id;
			$media_file->filename = "$this->serial_number$size_info[size_tag].jpg";
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
			$media_file->p_collection_ascii_id = $this->collection_ascii_id;
			$media_file->p_serial_number = $this->serial_number;
			$media_file->insert();
		}
	}

	function moveRawAsset($media_path) {
		$raw = "$media_path/raw/$this->serial_number";  
		rename($this->upload_path,$raw);
	}

	function placeAsset($item_id,$media_path) {
		if (file_exists("$media_path/$this->size")) {
			$asset_path = "$media_path/$this->size/$this->serial_number$this->ext";
		} else {
			echo "error creating $asset_path";
			return;
		}
		if (strstr($this->mime_type,'image/')) {
			$file_info = getimagesize($this->upload_path);
			$width = $file_info[0];
			$height = $file_info[1];
		} else {
			$width = 0;
			$heght = 0;
		}
		//archive doomed file
		if (file_exists($asset_path)) {
			$timestamp = time();
			$deleted = "$media_path/deleted/$timestamp$this->ext";
			rename($asset_path,$deleted);
		}
		rename($this->upload_path,$asset_path);
		//create the media_file entry
		$media_file = new DataObjects_Media_file;
		$media_file->item_id = $item_id;
		$media_file->filename = "$this->serial_number$this->ext";
		$media_file->file_size = "$this->file_size";
		$media_file->mime_type = $this->mime_type;
		$media_file->height = $height;
		$media_file->width = $width;
		$media_file->size = $this->size;
		$media_file->p_collection_ascii_id = $this->collection_ascii_id;
		$media_file->p_serial_number = $this->serial_number;
		$media_file->insert();
	}
}
public function get() {
	return file_get_contents($this->url,false,$this->ctx);
}

public function getCollectionInfo($ascii_id) {
	$url = $this->url . '/collection/' . $ascii_id;
	return file_get_contents($url,false,$this->ctx);
}

public function getAll() {
	$url = $this->url . '/collections';
	return file_get_contents($url,false,$this->ctx);
}

public function getAdminAttributes() {
	$url = $this->url . '/admin_attributes';
	return file_get_contents($url,false,$this->ctx);
}

public function getAttributes($ascii_id) {
	$url = $this->url . "/collection/$ascii_id/attributes";
	return file_get_contents($url,false,$this->ctx);
}

public function getItem($ser_num,$ascii_id) {
	$url = $this->url . "/collection/$ascii_id/item/$ser_num";
	return file_get_contents($url,false,$this->ctx);
}

public function getItemSerNums($ascii_id) {
	$url = $this->url . "/collection/$ascii_id/items?ser_nums=1";
	return file_get_contents($url,false,$this->ctx);
}
}
