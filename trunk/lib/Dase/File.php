<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 


abstract class Dase_File
{
	private static $types_map = array(
		//store in xml??
		'image/jpeg' => array('size' => 'jpeg', 'ext' => '.jpg','class'=>'Dase_File_Image'),
		'image/gif' => array('size' => 'gif', 'ext' => '.gif','class'=>'Dase_File_Image'),
		'image/png' => array('size' => 'png', 'ext' => '.png','class'=>'Dase_File_Image'),
		'image/tiff' => array('size' => 'tiff', 'ext' => '.tiff','class'=>'Dase_File_Image'),
		'audio/mpeg' => array('size' => 'mp3', 'ext' => '.mp3','class'=>'Dase_File_Audio'),
		'audio/mpg' => array('size' => 'mp3', 'ext' => '.mp3','class'=>'Dase_File_Audio'),
		'video/quicktime' => array('size' => 'quicktime', 'ext' => '.mov','class'=>'Dase_File_Video'),
		'application/pdf' => array('size' => 'pdf', 'ext' => '.pdf','class'=>'Dase_File_Pdf'),
		'application/xml' => array('size' => 'xml', 'ext' => '.xml','class'=>'Dase_File_Image'),
		'text/xml' => array('size' => 'xml', 'ext' => '.xml','class'=>'Dase_File_Image'),
		'application/xml+xslt' => array('size' => 'xslt', 'ext' => '.xsl','class'=>'Dase_File_Image'),
		'application/msword' => array('size' => 'doc', 'ext' => '.doc','class'=>'Dase_File_Doc'),
		'text/css' => array('size' => 'css', 'ext' => '.css','class'=>'Dase_File_Image'),
		'text/html' => array('size' => 'html', 'ext' => '.html','class'=>'Dase_File_Image'),
		'text/plain' => array('size' => 'html', 'ext' => '.html','class'=>'Dase_File_Image')
	);

	protected $metadata = array();
	protected $filepath;
	protected $file_size;
	protected $extension;
	protected $basename; //this INCLUDES the extension
	protected $filename;  //this is the basename minus the extension!!
	protected $mime_type;

	protected function __construct($file)
	{  //can only be called by subclass
		$this->filepath = $file;
		$this->file_size = filesize($file);
		$path_parts = pathinfo($file);
		$this->extension = $path_parts['extension'];
		$this->basename = $path_parts['basename'];
		if (isset($path_parts['filename'])) {
			$this->filename = $path_parts['filename']; // since PHP 5.2.0
		} else {
			$this->filename = str_replace("." . $this->extension,'',$path_parts['basename']);
		}
	}

	function getFilepath()
	{
		return $this->filepath;
	}

	function getFilename()
	{
		return $this->filename;
	}

	function getBasename()
	{
		return $this->basename;
	}

	abstract public function makeThumbnail($item,$collection);
	abstract public function makeViewitem($item,$collection);

	function getMetadata()
	{
		$this->metadata['admin_checksum'] = md5_file($this->filepath);
		$this->metadata['admin_file_size'] = $this->file_size;
		$this->metadata['admin_filename'] = $this->basename;
		$this->metadata['admin_last_modified_date_time'] = filemtime($this->filepath); 
		$this->metadata['admin_mime_type'] = $this->mime_type;
		$this->metadata['admin_filepath'] = $this->filepath;
		return $this->metadata;
	}	

	static function newFile($file)
	{
		$mime = Dase_File::getMimeType($file);
		if ($mime) {
			if (!isset(self::$types_map[$mime])) {
				throw new Exception("do not know about $mime mime type ($file)");
			}
			$df = new self::$types_map[$mime]['class']($file);
			$df->mime_type = $mime;
			return $df;
		} else {
			throw new Exception("cannot determin mime type for $file");
		}
	}

	static function newFileFromUrl($file)
	{
		$mime = Dase_File::getMimeType($file,1);

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
