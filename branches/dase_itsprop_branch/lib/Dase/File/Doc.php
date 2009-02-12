<?php

class Dase_File_Doc extends Dase_File
{
	protected $metadata = array();

	function __construct($file,$mime='')
	{
		parent::__construct($file,$mime);
	}

	function getMetadata()
	{
		$this->metadata = parent::getMetadata();
		return $this->metadata;
	}

	public function addToCollection($item,$check_for_dups) 
	{
		$media_file = parent::addToCollection($item,$check_for_dups);
		$this->makeThumbnail($item);
		$this->makeViewitem($item);
		return $media_file;
	}

}

