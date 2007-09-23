<?php

interface Dase_FileInterface
{
	function getFilepath();
	function getFilename();
	function getBasename();
	function getMetadata();
	function copyTo($location);
	function moveTo($location);
}
