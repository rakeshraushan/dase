<?php

class Dase_Handler_CategorySchemes extends Dase_Handler
{
	public $resource_map = array(
		'/' => 'category_schemes',
	);

	protected function setup($r)
	{
	}

	public function getCategorySchemes($r) 
	{
		$r->response_mime_type = 'application/atom+xml';
		$r->renderResponse(Dase_DBO_CategoryScheme::listAsFeed($this->db,$r->app_root));
	}

	//called from admin handler
	public function getCategorySchemesAtom($r) 
	{
		$r->renderResponse(Dase_DBO_CategoryScheme::listAsFeed($this->db,$r->app_root,'name'));
	}
}

