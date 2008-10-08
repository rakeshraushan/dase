<?php

class Dase_Handler_Tags extends Dase_Handler
{

	public $resource_map = array( 
		'/' => 'tags',
	);

	protected function setup($r)
	{
	}	

	public function postToTags($r)
	{
		$tag_name = $r->get('tag_name');
		//todo: make this work w/ cookie OR http auth??
		$user = $r->getUser();
		$tag = Dase_DBO_Tag::create($tag_name,$user);
		if ($tag) {
			//todo: should send a 201 w/ location header
			$r->renderResponse('Created "'.$tag_name.'"');
		} else {
			$r->renderError(409,'Please choose another name.');
		}
	}

}

