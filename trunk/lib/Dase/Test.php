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


class Dase_Test
{
	private $succeeded = 0;
	private $failed = 0;
	private $sx;
	public $results_xml;

	//might need to reimplement this as a singleton
	//how about as an Atom feed???????
	public function __construct()
	{
		$this->sx = simplexml_load_string('<tests/>');
	}

	function assertTrue($test,$name)
	{
		$test_xml = $this->sx->addChild('test');
		$test_xml->addAttribute('name',$name);
		if ($test) {
			$test_xml->addAttribute('result','success');
			$this->succeeded++;
		} else {
			$test_xml->addAttribute('result','failed');
			$this->failed++;
		}	
	}

	function asXml()
	{
		return $this->asSimpleXml()->asXml();
	}

	function asSimpleXml()
	{
		$result = $this->sx->addChild('result');
		$result->addChild('failed',$this->failed);
		$result->addChild('succeeded',$this->succeeded);
		$result->addChild('total',$this->succeeded+$this->failed);
		return $this->sx;
	}

	//todo: work on text-only tests also
}

