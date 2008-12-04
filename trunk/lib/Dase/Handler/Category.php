<?php

class Dase_Handler_Category extends Dase_Handler
{
	public $resource_map = array(
		'schemes' => 'schemes',
	);

	protected function setup($r)
	{
	}

	public function getSchemes($r) 
	{
		$r->response_mime_type = 'application/atom+xml';
		$r->renderResponse(Dase_DBO_CategoryScheme::listAsFeed());
	}

	public function getSchemesAtom($r) 
	{
		$r->renderResponse(Dase_DBO_CategoryScheme::listAsFeed('name'));
	}

}

