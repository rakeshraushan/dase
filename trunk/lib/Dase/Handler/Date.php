<?php

/** used to synchronize client & server */
class Dase_Handler_Date extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'date',
	);

	protected function setup($r)
	{
		$this->db = $r->retrieve('db');
	}

	public function getDate($r) {
		$r->renderResponse(date('Ymd',time()));
	}
}

