<?php

abstract class Dase_File
{
	public static $types_map = array(
		'image/jpeg' => array('size' => 'jpeg', 'ext' => 'jpg','class'=>'Dase_File_Image'),
		'image/gif' => array('size' => 'gif', 'ext' => 'gif','class'=>'Dase_File_Image'),
		'image/png' => array('size' => 'png', 'ext' => 'png','class'=>'Dase_File_Image'),
		'image/tiff' => array('size' => 'tiff', 'ext' => 'tif','class'=>'Dase_File_Image'),
	//	'image/tiff' => array('size' => 'tiff', 'ext' => 'tiff','class'=>'Dase_File_Image'),
		'audio/mpeg' => array('size' => 'mp3', 'ext' => 'mp3','class'=>'Dase_File_Audio'),
		'audio/mpg' => array('size' => 'mp3', 'ext' => 'mp3','class'=>'Dase_File_Audio'),
		'video/quicktime' => array('size' => 'quicktime', 'ext' => 'mov','class'=>'Dase_File_Video'),
		'application/pdf' => array('size' => 'pdf', 'ext' => 'pdf','class'=>'Dase_File_Pdf'),
		'application/xml' => array('size' => 'xml', 'ext' => 'xml','class'=>'Dase_File_Image'),
		'text/xml' => array('size' => 'xml', 'ext' => 'xml','class'=>'Dase_File_Image'),
		'application/xslt+xml' => array('size' => 'xslt', 'ext' => 'xsl','class'=>'Dase_File_Image'),
		'application/msword' => array('size' => 'doc', 'ext' => 'doc','class'=>'Dase_File_Doc'),
		'text/css' => array('size' => 'css', 'ext' => 'css','class'=>'Dase_File_Image'),
		'text/html' => array('size' => 'html', 'ext' => 'html','class'=>'Dase_File_Image'),
		'text/plain' => array('size' => 'html', 'ext' => 'html','class'=>'Dase_File_Image')
	);

	protected $metadata = array();
	protected $filepath;
	protected $file_size;
	protected $extension;
	protected $basename; //this INCLUDES the extension
	protected $filename;  //this is the basename minus the extension!!
	protected $mime_type;
	protected $orig_name;

	protected function __construct($file,$mime='')
	{  //can ONLY be called by subclass
		$this->filepath = $file;
		$this->file_size = filesize($file);
		$path_parts = pathinfo($file);
		if ($mime) {
			$this->mime_type = $mime;
			$this->extension = self::$types_map[$mime]['ext'];
		} else {
			//todo: will be a problem if no extention is returned in path_parts
			$this->extension = $path_parts['extension'];
		}
		$this->basename = $path_parts['basename'];
		if (isset($path_parts['filename'])) {
			$this->filename = $path_parts['filename']; // since PHP 5.2.0
		} else {
			$this->filename = str_replace("." . $this->extension,'',$path_parts['basename']);
		}
	}

	static function newFile($file,$mime='',$orig_name='')
	{
		if (!$mime) {
			$mime = Dase_File::getMimeType($file);
		}
		if ($mime) {
			if (!isset(self::$types_map[$mime])) {
				$orig_name = $orig_name ? $orig_name : $file;
				throw new Exception("DASe does not handle $mime mime type ($orig_name)");
			}
			//creates proper subclass
			$dasefile = new self::$types_map[$mime]['class']($file,$mime);
			$dasefile->size = self::$types_map[$mime]['size'];
			$dasefile->ext = self::$types_map[$mime]['ext'];
			$dasefile->mime_type = $mime;
			$dasefile->orig_name = $orig_name;
			return $dasefile;
		} else {
			throw new Exception("cannot determin mime type for $file");
		}
	}

	static function newFileFromUrl($file)
	{
		$mime = Dase_File::getMimeType($file,1);

	}

	function getFilepath()
	{
		return $this->filepath;
	}

	function getFilename()
	{
		return $this->filename;
	}

	function getFiletype()
	{
		return $this->mime_type;
	}

	function getFileSize()
	{
		return $this->file_size;
	}

	function getBasename()
	{
		return $this->basename;
	}

	function getOrigName()
	{
		return $this->orig_name;
	}

	abstract public function addToCollection(Dase_DBO_Collection $collection,Dase_DBO_Item $item,$check_for_dups);

	function getMetadata()
	{
		$this->metadata['md5'] = md5_file($this->filepath);
		$this->metadata['file_size'] = $this->file_size;
		$this->metadata['filename'] = $this->basename;
		$this->metadata['updated'] = date(DATE_ATOM,filemtime($this->filepath)); 
		$this->metadata['mime_type'] = $this->mime_type;
		return $this->metadata;
	}	

	static function getMimeType($file,$is_url = false)
	{
		if ($is_url) {
			$headers = get_headers($file);
			foreach ($headers as $hdr) {
				$matches = array();
				if (preg_match('@content-type:? ([a-zA-z/]*)@i',$hdr,$matches)) {
					return $matches[1];
				}
			}
		} else {
			$output = array();
			exec("file -i -b \"$file\"",$output);
			$matches = array();
			if (preg_match('@([a-zA-z/]*);?@i',$output[0],$matches)) {
				return $matches[1];
			}
		}
	}

	static function getMTime($file)
	{
		$stat = @stat($file);
		if($stat[9]) {
			return $stat[9];
		} else {
			return false;
		}
	}

	function copyTo($location)
	{
		if (copy($this->filepath,$location)) {
			return true;
		} else {
			throw new Exception("could not copy $this->filepath to $location");
		}
	}

	function moveTo($location)
	{
		if (rename($this->filepath,$location)) {
			return true;
		} else {
			throw new Exception("could not move $this->filepath to $location");
		}
	}
}
