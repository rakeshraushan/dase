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
	public $succeeded = 0;
	public $failed = 0;
	public $total = 0;
	public $result;
	public $name;

	function assertTrue($test,$name)
	{
		$this->name = $name;
		if ($test) {
			$this->result = 'succeeded';
			$this->succeeded++;
			$this->total++;
		} else {
			$this->result = 'failed';
			$this->failed++;
			$this->total++;
		}	
	}

	//todo: work on text-only tests also
}

