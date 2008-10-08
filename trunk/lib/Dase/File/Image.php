<?php

class Dase_File_Image extends Dase_File
{
	protected $metadata = array();
	protected $convert; //imagemagick

	function __construct($file,$mime='')
	{
		$this->convert = Dase_Config::get('convert');
		parent::__construct($file,$mime);
	}

	function addToCollection($item,$check_for_dups)
	{
		$collection = $item->getCollection();
		//check for multi-layered tiff
		if ('image/tiff' == $this->mime_type ){
			$image = new Imagick($this->filepath);
			if ($image->getNumberImages() > 1) {
				throw new Exception("Error: ".$title." appears to be a multi-layered tiff");
			}
		}
		$this->getMetadata();

		//prevents 2 files in same collection w/ same md5
		if ($check_for_dups) {
			$mf = new Dase_DBO_MediaFile;
			$mf->p_collection_ascii_id = $collection->ascii_id;
			$mf->md5 = $this->metadata['md5'];
			if ($mf->findOne()) {
				throw new Exception('duplicate file');
			}
		}
		$target = Dase_Config::get('path_to_media').'/'.$collection->ascii_id.'/'.$this->size.'/'.$item->serial_number.'.'.$this->ext;
		if (file_exists($target)) {
			//make a timestamped backup
			copy($target,$target.'.bak.'.time());
		}
		//should this be try-catch?
		if ($this->copyTo($target)) {
			$media_file = new Dase_DBO_MediaFile;
			$meta = array(
				'file_size','height','width','mime_type','updated','md5'
			);
			foreach ($meta as $term) {
				if (isset($this->metadata[$term])) {
					$media_file->$term = $this->metadata[$term];
				}
			}
			$media_file->item_id = $item->id;
			$media_file->filename = $item->serial_number.'.'.$this->ext;
			$media_file->size = $this->size;
			$media_file->p_serial_number = $item->serial_number;
			$media_file->p_collection_ascii_id = $collection->ascii_id;
			$media_file->insert();
			//will only insert item metadata when attribute name matches 'admin_'+att_name
			foreach ($this->metadata as $term => $text) {
				$item->setValue('admin_'.$term,$text);
			}
		}
		$rotate = 0;
		if (isset($this->metadata['exif_orientation'])) {
			if (6 == $this->metadata['exif_orientation']) {
				$rotate = 90;
			}
			if (8 == $this->metadata['exif_orientation']) {
				$rotate = 270;
			}
		}
		$this->makeThumbnail($item,$rotate);
		$this->makeViewitem($item,$rotate);
		$this->makeSizes($item,$rotate);
		return $media_file;
	}

	function getIptc()
	{   
		$iptc_metadata = array();
		$iptc_table['2#005'] = 'iptc_object_name';
		$iptc_table['2#015'] = 'iptc_category';
		$iptc_table['2#020'] = 'iptc_supplemental_category';
		$iptc_table['2#025'] = 'iptc_keywords';
		$iptc_table['2#055'] = 'iptc_date_created';
		$iptc_table['2#060'] = 'iptc_time_created';
		$iptc_table['2#062'] = 'iptc_digital_creation_date';
		$iptc_table['2#063'] = 'iptc_digital_creation_time';
		$iptc_table['2#065'] = 'iptc_originating_program';
		$iptc_table['2#070'] = 'iptc_program_version';
		$iptc_table['2#080'] = 'iptc_by_line';
		$iptc_table['2#085'] = 'iptc_by_line_title';
		$iptc_table['2#090'] = 'iptc_city';
		$iptc_table['2#092'] = 'iptc_sub_location';
		$iptc_table['2#095'] = 'iptc_province_state';
		$iptc_table['2#100'] = 'iptc_country_primary_location_code';
		$iptc_table['2#101'] = 'iptc_country_primary_location_name';
		$iptc_table['2#105'] = 'iptc_headline';
		$iptc_table['2#110'] = 'iptc_credit';
		$iptc_table['2#115'] = 'iptc_source';
		$iptc_table['2#116'] = 'iptc_copyright_notice';
		$iptc_table['2#118'] = 'iptc_contact';
		$iptc_table['2#120'] = 'iptc_caption_abstract';
		$iptc_table['2#122'] = 'iptc_caption_writer';
		$iptc_table['2#131'] = 'iptc_image_orientation';
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
		$this->metadata['width'] =  $size[0];
		$this->metadata['height'] = $size[1];
		return $this->metadata;
	}


	function getExif()
	{

		//exif_read_data only w/ jpg & tif
		if (strpos($this->mime_type,'jpg') ||
			strpos($this->mime_type, 'tif') ||
			strpos($this->mime_type, 'jpeg') ||
			strpos($this->mime_type, 'tiff'))
		{
			$exif_table['FileName'] = 'exif_filename';
			//$exif_table['FileDateTime'] = 'exif_filedatetime';
			$exif_table['FileSize'] = 'exif_filesize';
			$exif_table['FileType'] = 'exif_filetype';
			$exif_table['MimeType'] = 'exif_mimetype';
			$exif_table['Make'] = 'exif_make';
			$exif_table['Model'] = 'exif_model';
			$exif_table['Orientation'] = 'exif_orientation';
		//	$exif_table['XResolution'] = 'exif_xresolution';
		//	$exif_table['YResolution'] = 'exif_yresolution';
		//	$exif_table['ResolutionUnit'] = 'exif_resolutionunit';
			$exif_table['DateTime'] = 'exif_datetime';
		//	$exif_table['YCbCrPositioning'] = 'exif_ycbcrpositioning';
		//	$exif_table['Exif_IFD_Pointer'] = 'exif_ifd_pointer';
		//	$exif_table['ExposureTime'] = 'exif_exposuretime';
		//	$exif_table['FNumber'] = 'exif_fnumber';
		//	$exif_table['ExifVersion'] = 'exif_exifversion';
		//	$exif_table['DateTimeOriginal'] = 'exif_datetimeoriginal';
		//	$exif_table['DateTimeDigitized'] = 'exif_datetimedigitized';
		//	$exif_table['ComponentsConfiguration'] = 'exif_componentsconfiguration';
		//	$exif_table['CompressedBitsPerPixel'] = 'exif_compressedbitsperpixel';
		//	$exif_table['ShutterSpeedValue'] = 'exif_shutterspeedvalue';
		//	$exif_table['ApertureValue'] = 'exif_aperturevalue';
		//	$exif_table['ExposureBiasValue'] = 'exif_exposurebiasvalue';
		//	$exif_table['MaxApertureValue'] = 'exif_maxaperturevalue';
		//	$exif_table['MeteringMode'] = 'exif_meteringmode';
			$exif_table['ImageType'] = 'exif_imagetype';
		//	$exif_table['FirmwareVersion'] = 'exif_firmwareversion';
		//	$exif_table['ImageNumber'] = 'exif_imagenumber';
			$exif_table['OwnerName'] = 'exif_ownername';
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

	function makeThumbnail($item,$rotate)
	{
		$collection = $item->getCollection();
		$thumbnail = Dase_Config::get('path_to_media').'/'.$collection->ascii_id.'/thumbnail/'.$item->serial_number.'_100.jpg';  
		$results = exec("$this->convert \"$this->filepath\" -format jpeg -rotate $rotate -resize '100x100 >' -colorspace RGB $thumbnail");
		if (!file_exists($thumbnail)) {
			Dase_Log::info("failed to write $thumbnail");
		}
		$file_info = getimagesize($thumbnail);

		$media_file = new Dase_DBO_MediaFile;
		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number.'_100.jpg';
		if ($file_info) {
			$media_file->width = $file_info[0];
			$media_file->height = $file_info[1];
		}
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'thumbnail';
		$media_file->md5 = md5_file($thumbnail);
		$media_file->updated = date(DATE_ATOM);
		$media_file->file_size = filesize($thumbnail);
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

	function makeViewitem($item,$rotate)
	{
		$collection = $item->getCollection();
		$viewitem = Dase_Config::get('path_to_media').'/'.$collection->ascii_id.'/viewitem/'.$item->serial_number.'_400.jpg';  
		$results = exec("$this->convert \"$this->filepath\" -format jpeg -rotate $rotate -resize '400x400 >' -colorspace RGB $viewitem");
		if (!file_exists($viewitem)) {
			Dase_Log::info("failed to write $viewitem");
		}
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
		$media_file->md5 = md5_file($viewitem);
		$media_file->updated = date(DATE_ATOM);
		$media_file->file_size = filesize($viewitem);
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		Dase_Log::info("created $media_file->size $media_file->filename");
	}

	function makeSizes($item,$rotate)
	{
		$collection = $item->getCollection();
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
			$newimage = Dase_Config::get('path_to_media').'/'.$collection->ascii_id.'/'.$size.'/'.$item->serial_number.$size_info['size_tag'].'.jpg';  
			$command = "$this->convert \"$this->filepath\" -format jpeg -rotate $rotate -resize '$size_info[geometry] >' -colorspace RGB $newimage";
			$results = exec($command);
			if (!file_exists($newimage)) {
				Dase_Log::debug("failed to write $size image");
				Dase_Log::debug("UNSUCCESSFUL: $command");
			}
			$file_info = getimagesize($newimage);

			//create the media_file entry
			$media_file = new Dase_DBO_MediaFile;
			$media_file->item_id = $item->id;
			$media_file->filename = $item->serial_number.$size_info['size_tag'].".jpg";
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
			$media_file->md5 = md5_file($newimage);
			$media_file->updated = date(DATE_ATOM);
			$media_file->file_size = filesize($newimage);
			$media_file->p_collection_ascii_id = $collection->ascii_id;
			$media_file->p_serial_number = $item->serial_number;
			$media_file->insert();
			Dase_Log::info("created $media_file->size $media_file->filename");
		}
		return;
	}
}
