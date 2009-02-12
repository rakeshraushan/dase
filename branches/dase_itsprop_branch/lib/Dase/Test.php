<?php

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

