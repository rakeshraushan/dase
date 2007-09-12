<?php

class Dase_File_Image extends Dase_File
{
	protected $metadata = array();

	function __construct($file) {
		parent::__construct($file);
	}

	function getIptc() {   
		$iptc_metadata = array();
		$iptc_table['2#005'] = 'admin_iptc_object_name';
		$iptc_table['2#015'] = 'admin_iptc_category';
		$iptc_table['2#020'] = 'admin_iptc_supplemental_category';
		$iptc_table['2#025'] = 'admin_iptc_keywords';
		$iptc_table['2#055'] = 'admin_iptc_date_created';
		$iptc_table['2#060'] = 'admin_iptc_time_created';
		$iptc_table['2#062'] = 'admin_iptc_digital_creation_date';
		$iptc_table['2#063'] = 'admin_iptc_digital_creation_time';
		$iptc_table['2#065'] = 'admin_iptc_originating_program';
		$iptc_table['2#070'] = 'admin_iptc_program_version';
		$iptc_table['2#080'] = 'admin_iptc_by_line';
		$iptc_table['2#085'] = 'admin_iptc_by_line_title';
		$iptc_table['2#090'] = 'admin_iptc_city';
		$iptc_table['2#092'] = 'admin_iptc_sub_location';
		$iptc_table['2#095'] = 'admin_iptc_province_state';
		$iptc_table['2#100'] = 'admin_iptc_country_primary_location_code';
		$iptc_table['2#101'] = 'admin_iptc_country_primary_location_name';
		$iptc_table['2#105'] = 'admin_iptc_headline';
		$iptc_table['2#110'] = 'admin_iptc_credit';
		$iptc_table['2#115'] = 'admin_iptc_source';
		$iptc_table['2#116'] = 'admin_iptc_copyright_notice';
		$iptc_table['2#118'] = 'admin_iptc_contact';
		$iptc_table['2#120'] = 'admin_iptc_caption_abstract';
		$iptc_table['2#122'] = 'admin_iptc_caption_writer';
		$iptc_table['2#131'] = 'admin_iptc_image_orientation';
		$size = getimagesize ( $this->filepath, $info);       
		if(is_array($info) && isset($info["APP13"])) {   
			$iptc = iptcparse($info["APP13"]);
			foreach (array_keys($iptc) as $k) {             
				foreach($iptc[$k] as $val) {
					if (isset($iptc_table[$k]) && $val) {
						//NOTE THAT REPEAT FIELDS ARE OK!!!!!!!!!!!
						$iptc_metadata[$iptc_table[$k]][]  = $val;
					}
				}
			}                 
		}            
		foreach($iptc_metadata as $k => $v) {
			//collapse multiples into a csv
			$this->metadata[$k] = join(',',$v);
		}
		return $iptc_metadata;
	}

	function getMetadata() {
		$this->metadata = parent::getMetadata();
		$this->getIptc();
		$this->getExif();
		$size = getimagesize($this->filepath);
		$this->metadata['admin_image_width'] =  $size[0];
		$this->metadata['admin_image_height'] = $size[1];
		return $this->metadata;
	}


	function getExif() {
		/*
		$exif_table['FileName'] = 'admin_exif_XXXX';
		$exif_table['FileDateTime'] = 'admin_exif_XXXX';
		$exif_table['FileSize'] = 'admin_exif_XXXX';
		$exif_table['FileType'] = 'admin_exif_XXXX';
		$exif_table['MimeType'] = 'admin_exif_XXXX';
		$exif_table['Make'] = 'admin_exif_XXXX';
		$exif_table['Model'] = 'admin_exif_XXXX';
		$exif_table['Orientation'] = 'admin_exif_XXXX';
		$exif_table['XResolution'] = 'admin_exif_XXXX';
		$exif_table['YResolution'] = 'admin_exif_XXXX';
		$exif_table['ResolutionUnit'] = 'admin_exif_XXXX';
		$exif_table['DateTime'] = 'admin_exif_XXXX';
		$exif_table['YCbCrPositioning'] = 'admin_exif_XXXX';
		$exif_table['Exif_IFD_Pointer'] = 'admin_exif_XXXX';
		$exif_table['ExposureTime'] = 'admin_exif_XXXX';
		$exif_table['FNumber'] = 'admin_exif_XXXX';
		$exif_table['ExifVersion'] = 'admin_exif_XXXX';
		$exif_table['DateTimeOriginal'] = 'admin_exif_XXXX';
		$exif_table['DateTimeDigitized'] = 'admin_exif_XXXX';
		$exif_table['ComponentsConfiguration'] = 'admin_exif_XXXX';
		$exif_table['CompressedBitsPerPixel'] = 'admin_exif_XXXX';
		$exif_table['ShutterSpeedValue'] = 'admin_exif_XXXX';
		$exif_table['ApertureValue'] = 'admin_exif_XXXX';
		$exif_table['ExposureBiasValue'] = 'admin_exif_XXXX';
		$exif_table['MaxApertureValue'] = 'admin_exif_XXXX';
		$exif_table['MeteringMode'] = 'admin_exif_XXXX';
		$exif_table['ImageType'] = 'admin_exif_XXXX';
		$exif_table['FirmwareVersion'] = 'admin_exif_XXXX';
		$exif_table['ImageNumber'] = 'admin_exif_XXXX';
		$exif_table['OwnerName'] = 'admin_exif_XXXX';
		 */
		$exif_table['DateTime'] = 'admin_exif_datetime';
		$exif_metadata = array();
		$exif = exif_read_data($this->filepath);
		foreach ($exif as $k => $val) {
			if (isset($exif_table[$k]) && $val) {
				$exif_metadata[$exif_table[$k]] = $val;
			}
		}
		foreach ($exif_metadata as $k => $v) {
			$this->metadata[$k] = $v;
		}
		return $exif_metadata;
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

}
