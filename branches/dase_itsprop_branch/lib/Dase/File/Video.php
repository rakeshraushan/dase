<?php

class Dase_File_Video extends Dase_File
{
	protected $metadata = array();

	function __construct($file,$mime='')
	{
		parent::__construct($file,$mime);
	}

	public function addToCollection($item,$check_for_dups) 
	{
		$media_file = parent::addToCollection($item,$check_for_dups);
		$this->makeThumbnail($item);
		$this->makeViewitem($item);
		return $media_file;
	}

	function getMetadata()
	{
		//todo: figure out what other metadata we should get here
		return parent::getMetadata();
	}

}
