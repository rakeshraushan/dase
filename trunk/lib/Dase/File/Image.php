<?php

class Dase_File_Image extends Dase_File
{
	protected $metadata = array();

	function __construct($file)
	{
		parent::__construct($file);
	}

	function getIptc()
	{   
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
			if (is_array($iptc)) {
				foreach (array_keys($iptc) as $k) {             
					foreach($iptc[$k] as $val) {
						if (isset($iptc_table[$k]) && $val) {
							//NOTE THAT REPEAT FIELDS ARE OK!!!!!!!!!!!
							$iptc_metadata[$iptc_table[$k]][]  = $val;
						}
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

	function getMetadata()
	{
		$this->metadata = parent::getMetadata();
		$this->getIptc();
		$this->getExif();
		$size = getimagesize($this->filepath);
		$this->metadata['admin_image_width'] =  $size[0];
		$this->metadata['admin_image_height'] = $size[1];
		return $this->metadata;
	}


	function getExif()
	{

		//exif_read_data only gooss w/ jpg & tif
		if (strpos($this->mime_type,'jpg') ||
			strpos($this->mime_type, 'tif') ||
			strpos($this->mime_type, 'jpeg') ||
			strpos($this->mime_type, 'tiff'))
		{
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
			if (is_array($exif)) {
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
		}
	}

	function makeThumbnail($item,$collection)
	{
		$thumbnail = $collection->path_to_media_files . "/thumbnails/$item->serial_number" . '_100.jpg';  
		$results = exec("/usr/bin/convert \"$this->filepath\" -format jpeg -resize '100x100 >' -colorspace RGB $thumbnail");
		$file_info = getimagesize($thumbnail);

		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number . '_100.jpg';
		if ($file_info) {
			$media_file->width = $file_info[0];
			$media_file->height = $file_info[1];
		}
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'thumbnail';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		return "created $media_file->size $media_file->filename\n";
	}

	function makeViewitem($item,$collection)
	{
		$viewitem = $collection->path_to_media_files . "/400/$item->serial_number" . '_400.jpg';  
		$results = exec("/usr/bin/convert \"$this->filepath\" -format jpeg -resize '400x400 >' -colorspace RGB $viewitem");
		$file_info = getimagesize($viewitem);

		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number . '_400.jpg';
		if ($file_info) {
			$media_file->width = $file_info[0];
			$media_file->height = $file_info[1];
		}
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'viewitem';
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		return "created $media_file->size $media_file->filename\n";
	}

	function copyToMediaDir($item,$collection) {
		$dest = $collection->path_to_media_files.'/'.$this->size.'/'.$item->serial_number.$this->ext;
		$this->copyTo($dest);
		$file_info = getimagesize($dest);
		if ($file_info) {
		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number.$this->ext;
		$media_file->file_size = $this->file_size;
		$media_file->mime_type = $this->mime_type;
		$media_file->size = $this->size; 
		$media_file->width = $file_info[0];
		$media_file->height = $file_info[1];
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		return "created $media_file->filename\n";
		} else {
			//report error??????
		}
	}

	function makeSizes($item,$collection)
	{
		//todo: beware!!! this moves archival tifs into DASe!!
		$this->copyToMediaDir($item,$collection);

		$msg = '';
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
		$last_width = '';
		$last_height = '';
		foreach ($image_properties as $size => $size_info) {
			$newimage = $collection->path_to_media_files . "/$size/$item->serial_number{$size_info['size_tag']}.jpg";  
			$results = exec("/usr/bin/convert \"$this->filepath\" -format jpeg -resize '$size_info[geometry] >' -colorspace RGB $newimage");
			$file_info = getimagesize($newimage);

			//create the media_file entry
			$media_file = new Dase_DBO_MediaFile;
			$media_file->item_id = $item->id;
			$media_file->filename = "$item->serial_number{$size_info['size_tag']}.jpg";
			if ($file_info) {
				$media_file->width = $file_info[0];
				$media_file->height = $file_info[1];
			}

			if (($media_file->width <= $last_width) && ($media_file->height <= $last_height)) {
				return $msg;
			}

			$last_width = $media_file->width;
			$last_height = $media_file->height;
			$media_file->mime_type = 'image/jpeg';
			$media_file->size = $size;
			$media_file->p_collection_ascii_id = $collection->ascii_id;
			$media_file->p_serial_number = $item->serial_number;
			$media_file->insert();
			$msg .= "created $media_file->size $media_file->filename\n";
		}
		return $msg;
	}
}
