<?php

class Dase_Handler_Test extends Dase_Handler
{

	public $resource_map = array( 
		'/' => 'index',
		'auth' => 'require_auth',
	);

	protected function setup($r)
	{
	}	

	public function getIndex($r) 
	{
		$tpl = new Dase_Template($r);
		$r->renderResponse($tpl->fetch('test/index.tpl'));
	}


}

