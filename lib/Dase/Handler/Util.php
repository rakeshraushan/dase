<?php

/** this is mainly for one-off utility/maintenance scripts */

class Dase_Handler_Util extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'index',
		'index' => 'index',
	);

	protected function setup($r)
	{
	}

	public function getIndex($r) 
	{
		$r->renderResponse('hello utility');
	}

}

