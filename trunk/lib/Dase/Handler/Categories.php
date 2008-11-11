<?php

class Dase_Handler_Categories extends Dase_Handler
{
	//map uri_templates to resources
	//and create parameters based on templates
	public $resource_map = array(
		'/' => 'index',
	);

	protected function setup($r)
	{
	}

	public function getIndex($r) 
	{
		$r->response_mime_type = 'application/atomcat+xml';
		$r->renderResponse(Dase_DBO_Category::listAsXml());
	}

}

